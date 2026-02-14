<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Order::with('user:id,name,email,phone,telegram_username')
            ->select(['id', 'invoice_id', 'user_id', 'amount', 'product_quantity', 'payment_method', 'payment_status', 'order_status', 'created_at']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_id', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('admin/order/index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'order_status', 'payment_status']),
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load([
            'user:id,name,email,phone,telegram_username',
            'products.product:id,name,thumb_image,price',
        ]);

        return Inertia::render('admin/order/show', [
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'order_status' => ['required', 'string', 'in:pending,processing,shipped,delivered,cancelled'],
        ]);

        $order->update(['order_status' => $request->order_status]);

        return redirect()->back();
    }

    public function updatePaymentStatus(Order $order): RedirectResponse
    {
        $order->update(['payment_status' => !$order->payment_status]);

        return redirect()->back();
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()->back();
    }
}
