<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::where('status', true)
            ->where('is_approved', true)
            ->with(['category:id,name', 'brand:id,name'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if ($search = $request->input('search')) {
            $bigrams = $this->getBigrams($search);
            $words = array_filter(explode(' ', trim($search)));

            $query->where(function ($q) use ($search, $words, $bigrams) {
                // Exact substring match
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");

                // Multi-word: each word matches somewhere
                if (count($words) > 1) {
                    $q->orWhere(function ($sub) use ($words) {
                        foreach ($words as $word) {
                            $sub->where(function ($inner) use ($word) {
                                $inner->where('name', 'like', "%{$word}%")
                                    ->orWhere('short_description', 'like', "%{$word}%");
                            });
                        }
                    });
                }

                // Fuzzy bigram matching: at least 50% of bigrams match
                if (! empty($bigrams)) {
                    $minMatches = max(1, (int) ceil(count($bigrams) * 0.5));
                    $conditions = [];
                    $bindings = [];
                    foreach ($bigrams as $bigram) {
                        $conditions[] = '(LOWER(name) LIKE ?)';
                        $bindings[] = '%'.mb_strtolower($bigram).'%';
                    }

                    $q->orWhereRaw(
                        '('.implode(' + ', $conditions).') >= ?',
                        [...$bindings, $minMatches]
                    );
                }
            });
        }

        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        if ($subCategoryId = $request->input('sub_category')) {
            $query->where('sub_category_id', $subCategoryId);
        }

        if ($childCategoryId = $request->input('child_category')) {
            $query->where('child_category_id', $childCategoryId);
        }

        if ($brandId = $request->input('brand')) {
            $query->where('brand_id', $brandId);
        }

        if ($request->has('min_price') && $request->input('min_price') !== '') {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->has('max_price') && $request->input('max_price') !== '') {
            $query->where('price', '<=', $request->input('max_price'));
        }

        $sort = $request->input('sort', 'latest');
        // Push out-of-stock items to the bottom, then sort by user choice
        $query->orderByRaw('qty = 0');
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'popular' => $query->orderByDesc('reviews_count'),
            default => $query->latest(),
        };

        $paginated = $query->paginate(12)->withQueryString();
        $products = $paginated->currentPage() > 1
            ? Inertia::merge($paginated->items())
            : $paginated->items();

        $categories = Category::where('status', true)
            ->withCount(['products' => fn ($q) => $q->where('status', true)->where('is_approved', true)])
            ->whereHas('products', fn ($q) => $q->where('status', true)->where('is_approved', true))
            ->get(['id', 'name']);

        $brands = Brand::where('status', true)
            ->withCount(['products' => fn ($q) => $q->where('status', true)->where('is_approved', true)])
            ->whereHas('products', fn ($q) => $q->where('status', true)->where('is_approved', true))
            ->get(['id', 'name']);

        return Inertia::render('client/products/index', [
            'products' => $products,
            'productsMeta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
            ],
            'categories' => $categories,
            'brands' => $brands,
            'filters' => $request->only(['search', 'category', 'sub_category', 'child_category', 'brand', 'min_price', 'max_price', 'sort']),
        ]);
    }

    public function show(string $slug): Response
    {
        $product = Product::where('slug', $slug)
            ->where('status', true)
            ->where('is_approved', true)
            ->with([
                'category:id,name',
                'subCategory:id,name',
                'brand:id,name',
                'vendor.user:id,name',
                'images',
            ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->firstOrFail();

        $reviews = ProductReview::where('product_id', $product->id)
            ->where('status', true)
            ->with('user:id,name,avatar')
            ->latest()
            ->take(10)
            ->get();

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', true)
            ->where('is_approved', true)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->take(4)
            ->get();

        $isInWishlist = false;
        $isInCart = false;
        if (Auth::check()) {
            $isInWishlist = Auth::user()->wishlists()->where('product_id', $product->id)->exists();
            $isInCart = Auth::user()->carts()->where('product_id', $product->id)->exists();
        }

        return Inertia::render('client/products/show', [
            'product' => $product,
            'reviews' => $reviews,
            'relatedProducts' => $relatedProducts,
            'isInWishlist' => $isInWishlist,
            'isInCart' => $isInCart,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $query = Product::where('status', true)
            ->where('is_approved', true)
            ->select(['id', 'name', 'slug', 'thumb_image', 'price', 'offer_price', 'offer_start_date', 'offer_end_date', 'category_id', 'qty'])
            ->with('category:id,name');

        // Generate bigrams (2-char chunks) for fuzzy matching
        $bigrams = $this->getBigrams($q);

        if (! empty($bigrams)) {
            // Build a relevance score: count how many bigrams match in name
            $scoreExpression = [];
            $bindings = [];

            foreach ($bigrams as $bigram) {
                $scoreExpression[] = '(LOWER(name) LIKE ?)';
                $bindings[] = '%'.mb_strtolower($bigram).'%';
            }

            $scoreRaw = implode(' + ', $scoreExpression);

            // Also try exact LIKE as primary match
            $query->where(function ($sub) use ($q, $bigrams) {
                // Exact substring match (highest priority)
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('short_description', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");

                // Fuzzy: at least 50% of bigrams must match in name
                $minMatches = max(1, (int) ceil(count($bigrams) * 0.5));
                $bigramConditions = [];
                $bigramBindings = [];
                foreach ($bigrams as $bigram) {
                    $bigramConditions[] = '(LOWER(name) LIKE ?)';
                    $bigramBindings[] = '%'.mb_strtolower($bigram).'%';
                }

                $sub->orWhereRaw(
                    '('.implode(' + ', $bigramConditions).') >= ?',
                    [...$bigramBindings, $minMatches]
                );
            });

            // Order by relevance: exact match > starts with > bigram score > in-stock
            $products = $query
                ->orderByRaw('qty = 0')
                ->orderByRaw(
                    'CASE WHEN LOWER(name) LIKE ? THEN 0 WHEN LOWER(name) LIKE ? THEN 1 ELSE 2 END',
                    [mb_strtolower($q), mb_strtolower($q).'%']
                )
                ->orderByRaw("({$scoreRaw}) DESC", $bindings)
                ->limit(8)
                ->get();
        } else {
            $products = $query
                ->where('name', 'like', "%{$q}%")
                ->orderByRaw('qty = 0')
                ->limit(8)
                ->get();
        }

        return response()->json($products);
    }

    /**
     * Generate bigrams (2-character chunks) from a string for fuzzy matching.
     */
    private function getBigrams(string $str): array
    {
        $str = mb_strtolower(trim($str));
        $len = mb_strlen($str);
        $bigrams = [];

        for ($i = 0; $i < $len - 1; $i++) {
            $bigram = mb_substr($str, $i, 2);
            if (trim($bigram) !== '' && mb_strlen(trim($bigram)) === 2) {
                $bigrams[] = $bigram;
            }
        }

        return array_unique($bigrams);
    }

    public function submitReview(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:1000',
        ]);

        ProductReview::updateOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $product->id],
            ['rating' => $validated['rating'], 'review' => $validated['review'], 'status' => false],
        );

        return redirect()->back();
    }
}
