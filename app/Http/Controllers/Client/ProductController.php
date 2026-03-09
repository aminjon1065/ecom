<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\ProductIndexRequest;
use App\Http\Requests\Client\SubmitProductReviewRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\PopularSearchQuery;
use App\Models\PriceAlert;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductViewEvent;
use App\Services\Product\ProductFilter;
use App\Services\Product\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductFilter $productFilter,
        private readonly RecommendationService $recommendationService,
    ) {}

    public function index(ProductIndexRequest $request): Response
    {
        $filters = $request->validated();

        $query = Product::where('status', true)
            ->where('is_approved', true)
            ->with(['category:id,name', 'brand:id,name'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        $this->productFilter->apply($query, $filters);

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
            'filters' => collect($filters)
                ->only(['search', 'category', 'sub_category', 'child_category', 'brand', 'min_price', 'max_price', 'sort'])
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->all(),
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

        ProductViewEvent::query()->create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'viewed_at' => now(),
        ]);

        $reviews = ProductReview::where('product_id', $product->id)
            ->where('status', true)
            ->with('user:id,name,avatar')
            ->latest()
            ->take(10)
            ->get();

        $relatedProducts = $this->recommendationService->relatedProducts($product, 4);
        $alsoBoughtProducts = $this->recommendationService->alsoBoughtProducts($product, 4);

        $isAuthenticated = Auth::check();
        $canReviewProduct = false;
        $isPriceAlertActive = false;
        $isInWishlist = false;
        $isInCart = false;

        if ($isAuthenticated) {
            $userId = (int) Auth::id();

            $isInWishlist = Auth::user()->wishlists()->where('product_id', $product->id)->exists();
            $isInCart = Auth::user()->carts()->where('product_id', $product->id)->exists();
            $isPriceAlertActive = PriceAlert::query()
                ->where('user_id', $userId)
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->exists();
            $canReviewProduct = Order::query()
                ->where('user_id', $userId)
                ->where('order_status', '!=', 'cancelled')
                ->whereHas('products', function ($query) use ($product): void {
                    $query->where('product_id', $product->id);
                })
                ->exists();
        }

        $effectivePrice = ($product->offer_price && $product->offer_start_date && $product->offer_end_date && now()->between($product->offer_start_date, $product->offer_end_date))
            ? (float) $product->offer_price
            : (float) $product->price;

        return Inertia::render('client/products/show', [
            'product' => $product,
            'reviews' => $reviews,
            'relatedProducts' => $relatedProducts,
            'alsoBoughtProducts' => $alsoBoughtProducts,
            'isAuthenticated' => $isAuthenticated,
            'canReviewProduct' => $canReviewProduct,
            'isPriceAlertActive' => $isPriceAlertActive,
            'isInWishlist' => $isInWishlist,
            'isInCart' => $isInCart,
            'deliveryEstimate' => $this->resolveDeliveryEstimate((int) $product->qty),
            'seo' => [
                'title' => $product->seo_title ?: $product->name,
                'description' => $product->seo_description ?: $product->short_description,
                'image' => $product->thumb_image ? asset('storage/'.$product->thumb_image) : null,
                'price' => $effectivePrice,
                'currency' => 'KGS',
                'availability' => $product->qty > 0 ? 'InStock' : 'OutOfStock',
                'sku' => $product->sku,
                'brand' => $product->brand?->name,
            ],
        ]);
    }

    private function resolveDeliveryEstimate(int $qty): ?string
    {
        if ($qty <= 0) {
            return null;
        }

        if ($qty >= 20) {
            return 'Доставка завтра';
        }

        if ($qty >= 5) {
            return 'Доставка 1-2 дня';
        }

        return 'Доставка 2-4 дня';
    }

    public function search(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $query = Product::where('status', true)
            ->where('is_approved', true)
            ->select(['id', 'name', 'slug', 'thumb_image', 'price', 'offer_price', 'offer_start_date', 'offer_end_date', 'category_id', 'brand_id', 'qty'])
            ->with(['category:id,name', 'brand:id,name']);

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

    public function popularSearches(): JsonResponse
    {
        $manualQueries = PopularSearchQuery::query()
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->limit(8)
            ->pluck('query')
            ->map(fn (string $query): string => trim($query))
            ->filter(fn (string $query): bool => $query !== '')
            ->values();

        if (! $manualQueries->isEmpty()) {
            return response()->json($manualQueries);
        }

        $popularQueries = ProductViewEvent::query()
            ->join('products', 'products.id', '=', 'product_view_events.product_id')
            ->where('products.status', true)
            ->where('products.is_approved', true)
            ->groupBy('products.id', 'products.name')
            ->select('products.name')
            ->selectRaw('count(*) as views_count')
            ->orderByDesc('views_count')
            ->limit(8)
            ->pluck('name')
            ->map(fn (string $name): string => trim($name))
            ->filter(fn (string $name): bool => $name !== '')
            ->values();

        if ($popularQueries->isEmpty()) {
            $popularQueries = Product::query()
                ->where('status', true)
                ->where('is_approved', true)
                ->latest('id')
                ->limit(8)
                ->pluck('name')
                ->map(fn (string $name): string => trim($name))
                ->filter(fn (string $name): bool => $name !== '')
                ->values();
        }

        return response()->json($popularQueries);
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

    public function submitReview(SubmitProductReviewRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        ProductReview::updateOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $product->id],
            [
                'rating' => $validated['rating'],
                'review' => $validated['review'],
                'status' => false,
                'verified_purchase' => true,
            ],
        );

        return redirect()->back();
    }
}
