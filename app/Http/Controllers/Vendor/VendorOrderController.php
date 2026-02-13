<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class VendorOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $vendor = Auth::user()->vendor;
        $productIds = Product::where('vendor_id', $vendor->id)->pluck('id');

        $query = Order::whereHas('products', fn($q) => $q->whereIn('product_id', $productIds))
            ->with('user:id,name,email');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_id', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('order_status')) {
            $query->where('order_status', $status);
        }

        if ($request->has('payment_status') && $request->input('payment_status') !== '') {
            $query->where('payment_status', $request->boolean('payment_status'));
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('vendor/order/index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'order_status', 'payment_status']),
        ]);
    }

    public function show(Order $order): Response
    {
        $vendor = Auth::user()->vendor;
        $productIds = Product::where('vendor_id', $vendor->id)->pluck('id');

        // Only show order products that belong to this vendor
        $order->load([
            'user:id,name,email',
            'products' => fn($q) => $q->whereIn('product_id', $productIds),
            'products.product:id,name,thumb_image,price',
        ]);

        return Inertia::render('vendor/order/show', [
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $vendor = Auth::user()->vendor;
        $productIds = Product::where('vendor_id', $vendor->id)->pluck('id');

        // Ensure vendor has products in this order
        abort_unless(
            $order->products()->whereIn('product_id', $productIds)->exists(),
            403
        );

        $validated = $request->validate([
            'order_status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->update($validated);

        return redirect()->back();
    }
}
