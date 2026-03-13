<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateVendorProfileRequest;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class VendorController extends Controller
{
    public function dashboard(): Response
    {
        $vendor = Auth::user()->vendor;

        if (! $vendor) {
            return Inertia::render('vendor/dashboard', [
                'statistics' => null,
                'recentOrders' => [],
                'topProducts' => [],
            ]);
        }

        $productIds = Product::where('vendor_id', $vendor->id)->pluck('id');

        // 1 query: merge total/approved/pending product counts.
        $productStats = Product::where('vendor_id', $vendor->id)
            ->selectRaw('COUNT(*) as total, SUM(is_approved = 1) as approved, SUM(is_approved = 0) as pending')
            ->first();

        // 1 query: merge today / yesterday / total revenue via conditional aggregation.
        $revenueStats = OrderProduct::whereIn('order_products.product_id', $productIds)
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->where('orders.payment_status', true)
            ->selectRaw('
                SUM(order_products.quantity * order_products.unit_price) as total_revenue,
                SUM(CASE WHEN DATE(orders.created_at) = CURDATE()
                    THEN order_products.quantity * order_products.unit_price ELSE 0 END) as today_revenue,
                SUM(CASE WHEN DATE(orders.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                    THEN order_products.quantity * order_products.unit_price ELSE 0 END) as yesterday_revenue
            ')
            ->first();

        // 1 query: merge total / pending order counts.
        $orderStats = Order::whereHas('products', fn ($q) => $q->whereIn('product_id', $productIds))
            ->selectRaw('COUNT(*) as total, SUM(order_status = ?) as pending', [OrderStatus::Pending->value])
            ->first();

        // 1 query: review count + average rating.
        $reviewStats = ProductReview::whereIn('product_id', $productIds)
            ->selectRaw('COUNT(*) as total, AVG(CASE WHEN status = 1 THEN rating END) as avg_rating')
            ->first();

        $statistics = [
            'total_revenue' => (float) ($revenueStats?->total_revenue ?? 0),
            'today_revenue' => (float) ($revenueStats?->today_revenue ?? 0),
            'yesterday_revenue' => (float) ($revenueStats?->yesterday_revenue ?? 0),
            'total_orders' => (int) ($orderStats?->total ?? 0),
            'pending_orders' => (int) ($orderStats?->pending ?? 0),
            'total_products' => (int) ($productStats?->total ?? 0),
            'approved_products' => (int) ($productStats?->approved ?? 0),
            'pending_products' => (int) ($productStats?->pending ?? 0),
            'total_reviews' => (int) ($reviewStats?->total ?? 0),
            'average_rating' => round((float) ($reviewStats?->avg_rating ?? 0), 1),
        ];

        $recentOrders = Order::whereHas('products', fn ($q) => $q->whereIn('product_id', $productIds))
            ->with('user:id,name,email')
            ->latest()
            ->take(10)
            ->get(['id', 'invoice_id', 'user_id', 'grand_total', 'product_quantity', 'payment_method', 'payment_status', 'order_status', 'created_at']);

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

    public function updateProfile(UpdateVendorProfileRequest $request): RedirectResponse
    {
        $vendor = Auth::user()->vendor;

        $validated = $request->validated();

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
