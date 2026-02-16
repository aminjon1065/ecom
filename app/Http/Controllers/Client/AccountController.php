<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserAddress;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function dashboard(): Response
    {
        $user = Auth::user();

        $totalOrders = Order::where('user_id', $user->id)->count();
        $pendingOrders = Order::where('user_id', $user->id)->where('order_status', 'pending')->count();
        $completedOrders = Order::where('user_id', $user->id)->where('order_status', 'delivered')->count();
        $totalSpent = Order::where('user_id', $user->id)->where('payment_status', true)->sum('amount');

        $recentOrders = Order::where('user_id', $user->id)
            ->with('products.product:id,name,thumb_image')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('client/account/dashboard', [
            'stats' => [
                'totalOrders' => $totalOrders,
                'pendingOrders' => $pendingOrders,
                'completedOrders' => $completedOrders,
                'totalSpent' => $totalSpent,
            ],
            'recentOrders' => $recentOrders,
        ]);
    }

    public function orders(): Response
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('products.product:id,name,thumb_image')
            ->latest()
            ->paginate(10);

        return Inertia::render('client/account/orders', [
            'orders' => $orders,
        ]);
    }

    public function orderShow(Order $order): Response
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $order->load('products.product:id,name,slug,thumb_image,price');

        return Inertia::render('client/account/order-show', [
            'order' => $order,
        ]);
    }

    public function downloadInvoice(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $order->load(['products.product:id,name', 'user:id,name']);

        $pdf = Pdf::loadView('invoices.order-invoice', [
            'order' => $order,
            'title' => 'Чек #' . $order->invoice_id,
        ]);

        return $pdf->download("invoice-{$order->invoice_id}.pdf");
    }

    public function addresses(): Response
    {
        $addresses = UserAddress::where('user_id', Auth::id())->latest()->get();

        return Inertia::render('client/account/addresses', [
            'addresses' => $addresses,
        ]);
    }

    public function profile(): Response
    {
        return Inertia::render('client/account/profile');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        Auth::user()->update($validated);

        return redirect()->back();
    }
}
