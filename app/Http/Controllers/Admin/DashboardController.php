<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DashboardMetricsRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductViewEvent;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index(DashboardMetricsRequest $request): Response
    {
        $period = $request->validated('period', '30');
        $periodStart = $this->resolvePeriodStart($period);

        $todayRevenue = Order::where('payment_status', true)
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        $yesterdayRevenue = Order::where('payment_status', true)
            ->whereDate('created_at', Carbon::yesterday())
            ->sum('amount');

        $hasUserRole = Role::query()
            ->where('name', 'user')
            ->where('guard_name', 'web')
            ->exists();

        $statistics = [
            'total_revenue' => Order::where('payment_status', true)->sum('amount'),
            'today_revenue' => $todayRevenue,
            'yesterday_revenue' => $yesterdayRevenue,
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('order_status', 'pending')->count(),
            'total_products' => Product::count(),
            'pending_products' => Product::where('is_approved', false)->count(),
            'total_customers' => $hasUserRole ? User::role('user')->count() : User::count(),
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

        $viewerUsersQuery = ProductViewEvent::query()
            ->whereNotNull('user_id');
        $cartUsersQuery = Cart::query();
        $orderUsersQuery = Order::query()
            ->where('order_status', '!=', 'cancelled');

        if ($periodStart !== null) {
            $viewerUsersQuery->where('viewed_at', '>=', $periodStart);
            $cartUsersQuery->where('created_at', '>=', $periodStart);
            $orderUsersQuery->where('created_at', '>=', $periodStart);
        }

        $viewerUsers = $viewerUsersQuery
            ->distinct('user_id')
            ->count('user_id');
        $cartUsers = $cartUsersQuery
            ->distinct('user_id')
            ->count('user_id');
        $orderUsers = $orderUsersQuery
            ->distinct('user_id')
            ->count('user_id');

        $funnelMetrics = [
            'viewers' => $viewerUsers,
            'cart_users' => $cartUsers,
            'buyers' => $orderUsers,
            'view_to_cart' => $viewerUsers > 0 ? round(($cartUsers / $viewerUsers) * 100, 2) : 0.0,
            'cart_to_order' => $cartUsers > 0 ? round(($orderUsers / $cartUsers) * 100, 2) : 0.0,
            'view_to_order' => $viewerUsers > 0 ? round(($orderUsers / $viewerUsers) * 100, 2) : 0.0,
        ];

        $topProductsQuery = Product::query()
            ->join('order_products', 'order_products.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->where('orders.order_status', '!=', 'cancelled')
            ->groupBy('products.id', 'products.name', 'products.thumb_image')
            ->select('products.id', 'products.name', 'products.thumb_image')
            ->selectRaw('sum(order_products.quantity) as sold_qty')
            ->selectRaw('sum(order_products.line_total) as gross_revenue')
            ->selectRaw('count(distinct order_products.order_id) as orders_count')
            ->orderByDesc('sold_qty')
            ->limit(5);

        if ($periodStart !== null) {
            $topProductsQuery->where('orders.created_at', '>=', $periodStart);
        }

        $topProducts = $topProductsQuery
            ->get()
            ->map(function ($item) {
                $item->sold_qty = (int) $item->sold_qty;
                $item->orders_count = (int) $item->orders_count;
                $item->gross_revenue = (float) $item->gross_revenue;

                return $item;
            });

        return Inertia::render('admin/dashboard', [
            'statistics' => $statistics,
            'orderStats' => $orderStats,
            'pendingVendors' => $pendingVendors,
            'pendingProducts' => $pendingProducts,
            'recentOrders' => $recentOrders,
            'pendingReviews' => $pendingReviews,
            'vendorProducts' => $vendorProducts,
            'vendorProductStats' => $vendorProductStats,
            'metricsPeriod' => $period,
            'funnelMetrics' => $funnelMetrics,
            'topProducts' => $topProducts,
        ]);
    }

    private function resolvePeriodStart(string $period): ?Carbon
    {
        return match ($period) {
            '7' => now()->subDays(7),
            '30' => now()->subDays(30),
            '90' => now()->subDays(90),
            default => null,
        };
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
