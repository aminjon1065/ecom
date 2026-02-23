<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $todayRevenue = Order::where('payment_status', true)
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        $yesterdayRevenue = Order::where('payment_status', true)
            ->whereDate('created_at', Carbon::yesterday())
            ->sum('amount');

        $statistics = [
            'total_revenue' => Order::where('payment_status', true)->sum('amount'),
            'today_revenue' => $todayRevenue,
            'yesterday_revenue' => $yesterdayRevenue,
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('order_status', 'pending')->count(),
            'total_products' => Product::count(),
            'pending_products' => Product::where('is_approved', false)->count(),
            'total_customers' => User::role('user')->count(),
            'total_vendors' => Vendor::count(),
            'pending_vendors' => Vendor::where('status', false)->count(),
            'total_reviews' => ProductReview::count(),
            'pending_reviews' => ProductReview::where('status', false)->count(),
        ];

        $orderStats = Order::select('order_status', DB::raw('count(*) as count'))
            ->groupBy('order_status')
            ->pluck('count', 'order_status')
            ->toArray();

        $pendingVendors = Vendor::where('status', false)
            ->with('user:id,name,email,avatar')
            ->latest()
            ->take(5)
            ->get(['id', 'user_id', 'shop_name', 'created_at']);

        $pendingProducts = Product::where('is_approved', false)
            ->with(['vendor.user:id,name', 'category:id,name'])
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'thumb_image', 'price', 'vendor_id', 'category_id', 'created_at']);

        $recentOrders = Order::with('user:id,name,email')
            ->latest()
            ->take(10)
            ->get(['id', 'invoice_id', 'user_id', 'amount', 'product_quantity', 'payment_method', 'payment_status', 'order_status', 'created_at']);

        $pendingReviews = ProductReview::where('status', false)
            ->with(['product:id,name', 'user:id,name'])
            ->latest()
            ->take(5)
            ->get(['id', 'product_id', 'user_id', 'review', 'rating', 'created_at']);

        $vendorProducts = Product::whereNotNull('vendor_id')
            ->with(['vendor.user:id,name', 'category:id,name'])
            ->latest()
            ->take(10)
            ->get(['id', 'name', 'thumb_image', 'price', 'qty', 'vendor_id', 'category_id', 'is_approved', 'status', 'created_at']);

        $vendorProductStats = [
            'total' => Product::whereNotNull('vendor_id')->count(),
            'approved' => Product::whereNotNull('vendor_id')->where('is_approved', true)->count(),
            'pending' => Product::whereNotNull('vendor_id')->where('is_approved', false)->count(),
            'active' => Product::whereNotNull('vendor_id')->where('status', true)->count(),
        ];

        return Inertia::render('admin/dashboard', [
            'statistics' => $statistics,
            'orderStats' => $orderStats,
            'pendingVendors' => $pendingVendors,
            'pendingProducts' => $pendingProducts,
            'recentOrders' => $recentOrders,
            'pendingReviews' => $pendingReviews,
            'vendorProducts' => $vendorProducts,
            'vendorProductStats' => $vendorProductStats,
        ]);
    }

    public function approveVendor(Vendor $vendor): RedirectResponse
    {
        $vendor->update(['status' => true]);

        Log::info('Admin approved vendor', [
            'admin_id' => Auth::id(),
            'vendor_id' => $vendor->id,
            'shop_name' => $vendor->shop_name,
        ]);

        return redirect()->back();
    }

    public function rejectVendor(Vendor $vendor): RedirectResponse
    {
        Log::warning('Admin rejected (deleted) vendor', [
            'admin_id' => Auth::id(),
            'vendor_id' => $vendor->id,
            'shop_name' => $vendor->shop_name,
        ]);

        $vendor->delete();

        return redirect()->back();
    }

    public function approveProduct(Product $product): RedirectResponse
    {
        $product->update(['is_approved' => true]);

        Log::info('Admin approved product', [
            'admin_id' => Auth::id(),
            'product_id' => $product->id,
            'name' => $product->name,
            'vendor_id' => $product->vendor_id,
        ]);

        return redirect()->back();
    }

    public function approveReview(ProductReview $productReview): RedirectResponse
    {
        $productReview->update(['status' => true]);

        Log::info('Admin approved review', [
            'admin_id' => Auth::id(),
            'review_id' => $productReview->id,
        ]);

        return redirect()->back();
    }

    public function deleteReview(ProductReview $productReview): RedirectResponse
    {
        Log::warning('Admin deleted review', [
            'admin_id' => Auth::id(),
            'review_id' => $productReview->id,
        ]);

        $productReview->delete();

        return redirect()->back();
    }
}
