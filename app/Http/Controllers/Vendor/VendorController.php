<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class VendorController extends Controller
{
    public function dashboard(): Response
    {
        $vendor = Auth::user()->vendor;

        if (!$vendor) {
            return Inertia::render('vendor/dashboard', [
                'statistics' => null,
                'recentOrders' => [],
                'topProducts' => [],
            ]);
        }

        $productIds = Product::where('vendor_id', $vendor->id)->pluck('id');

        $todayRevenue = OrderProduct::whereIn('product_id', $productIds)
            ->whereHas('order', fn($q) => $q->where('payment_status', true)->whereDate('created_at', Carbon::today()))
            ->sum(DB::raw('quantity * unit_price'));

        $yesterdayRevenue = OrderProduct::whereIn('product_id', $productIds)
            ->whereHas('order', fn($q) => $q->where('payment_status', true)->whereDate('created_at', Carbon::yesterday()))
            ->sum(DB::raw('quantity * unit_price'));

        $totalRevenue = OrderProduct::whereIn('product_id', $productIds)
            ->whereHas('order', fn($q) => $q->where('payment_status', true))
            ->sum(DB::raw('quantity * unit_price'));

        $totalOrders = Order::whereHas('products', fn($q) => $q->whereIn('product_id', $productIds))
            ->count();

        $pendingOrders = Order::where('order_status', 'pending')
            ->whereHas('products', fn($q) => $q->whereIn('product_id', $productIds))
            ->count();

        $statistics = [
            'total_revenue' => $totalRevenue,
            'today_revenue' => $todayRevenue,
            'yesterday_revenue' => $yesterdayRevenue,
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'total_products' => Product::where('vendor_id', $vendor->id)->count(),
            'approved_products' => Product::where('vendor_id', $vendor->id)->where('is_approved', true)->count(),
            'pending_products' => Product::where('vendor_id', $vendor->id)->where('is_approved', false)->count(),
            'total_reviews' => ProductReview::whereIn('product_id', $productIds)->count(),
            'average_rating' => round(ProductReview::whereIn('product_id', $productIds)->where('status', true)->avg('rating') ?? 0, 1),
        ];

        $recentOrders = Order::whereHas('products', fn($q) => $q->whereIn('product_id', $productIds))
            ->with('user:id,name,email')
            ->latest()
            ->take(10)
            ->get(['id', 'invoice_id', 'user_id', 'amount', 'product_quantity', 'payment_method', 'payment_status', 'order_status', 'created_at']);

        $topProducts = Product::where('vendor_id', $vendor->id)
            ->where('is_approved', true)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_count')
            ->take(5)
            ->get(['id', 'name', 'thumb_image', 'price', 'qty', 'status']);

        return Inertia::render('vendor/dashboard', [
            'statistics' => $statistics,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
        ]);
    }

    public function profile(): Response
    {
        $vendor = Auth::user()->vendor;

        return Inertia::render('vendor/profile', [
            'vendor' => $vendor,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $vendor = Auth::user()->vendor;

        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'address' => 'nullable|string|max:500',
            'banner' => 'nullable|image|max:2048',
            'facebook_url' => 'nullable|url|max:255',
            'telegram_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
        ]);

        if ($request->hasFile('banner')) {
            if ($vendor->banner) {
                Storage::disk('public')->delete($vendor->banner);
            }
            $validated['banner'] = $request->file('banner')->store('vendors', 'public');
        }

        $vendor->update($validated);

        return redirect()->back();
    }
}
