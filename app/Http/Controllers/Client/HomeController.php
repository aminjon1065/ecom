<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\Slider;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function home(): Response
    {
        $sliders = Slider::where('status', true)
            ->orderBy('serial')
            ->get();

        $flashSaleProducts = FlashSale::where('status', true)
            ->where('show_at_main', true)
            ->where('end_date', '>=', now())
            ->with(['product' => fn($q) => $q->where('status', true)->where('is_approved', true)])
            ->get()
            ->pluck('product')
            ->filter()
            ->values();

        $newProducts = Product::where('status', true)
            ->where('is_approved', true)
            ->where('product_type', 'new')
            ->with('category:id,name')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->take(8)
            ->get();

        $topProducts = Product::where('status', true)
            ->where('is_approved', true)
            ->where('product_type', 'top')
            ->with('category:id,name')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->take(8)
            ->get();

        $bestProducts = Product::where('status', true)
            ->where('is_approved', true)
            ->where('product_type', 'best')
            ->with('category:id,name')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::where('status', true)
            ->withCount(['products' => fn($q) => $q->where('status', true)->where('is_approved', true)])
            ->orderByDesc('products_count')
            ->take(8)
            ->get(['id', 'name', 'icon']);

        return Inertia::render('welcome', [
            'sliders' => $sliders,
            'flashSaleProducts' => $flashSaleProducts,
            'newProducts' => $newProducts,
            'topProducts' => $topProducts,
            'bestProducts' => $bestProducts,
            'categories' => $categories
        ]);
    }
}
