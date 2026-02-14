<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
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
            ->orderByRaw('qty = 0, name')
            ->withCount('reviews');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
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
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'popular' => $query->orderByDesc('reviews_count'),
            default => $query->latest(),
        };

        $paginated = $query->paginate(12)->withQueryString();

        $categories = Category::where('status', true)
            ->withCount(['products' => fn($q) => $q->where('status', true)->where('is_approved', true)])
            ->having('products_count', '>', 0)
            ->get(['id', 'name']);

        $brands = Brand::get(['id', 'name']);

        return Inertia::render('client/products/index', [
            'products' => Inertia::merge($paginated->items()),
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
