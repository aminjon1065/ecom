<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\AccountOrderIndexRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\UserAddress;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function dashboard(AccountOrderIndexRequest $request): Response
    {
        $user = Auth::user();
        $filters = $request->validated();

        $totalOrders = Order::where('user_id', $user->id)->count();
        $pendingOrders = Order::where('user_id', $user->id)->where('order_status', 'pending')->count();
        $completedOrders = Order::where('user_id', $user->id)->where('order_status', 'delivered')->count();
        $totalSpent = Order::where('user_id', $user->id)->where('payment_status', true)->sum('amount');

        $recentOrdersQuery = Order::query()
            ->where('user_id', $user->id)
            ->with('products.product:id,name,thumb_image')
            ->latest();

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $recentOrdersQuery->where('invoice_id', 'like', "%{$search}%");
        }

        if (! empty($filters['status'])) {
            $recentOrdersQuery->where('order_status', $filters['status']);
        }

        $recentOrders = $recentOrdersQuery
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
            'dashboardFilters' => collect($filters)
                ->only(['search', 'status'])
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->all(),
        ]);
    }

    public function orders(AccountOrderIndexRequest $request): Response
    {
        $filters = $request->validated();

        $query = Order::query()
            ->where('user_id', Auth::id())
            ->with('products.product:id,name,thumb_image')
            ->latest();

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('invoice_id', 'like', "%{$search}%");
        }

        if (! empty($filters['status'])) {
            $query->where('order_status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $orders = $query->paginate(10)->withQueryString();

        return Inertia::render('client/account/orders', [
            'orders' => $orders,
            'filters' => collect($filters)
                ->only(['search', 'status', 'date_from', 'date_to'])
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->all(),
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

    public function repeatOrder(Order $order): RedirectResponse
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $order->load('products');

        $addedItems = 0;

        foreach ($order->products as $orderItem) {
            /** @var Product|null $product */
            $product = Product::query()
                ->where('id', $orderItem->product_id)
                ->where('status', true)
                ->where('is_approved', true)
                ->first();

            if (! $product || $product->qty < 1) {
                continue;
            }

            $existingCart = Cart::query()
                ->where('user_id', Auth::id())
                ->where('product_id', $product->id)
                ->first();

            $targetQty = min((int) $orderItem->quantity, (int) $product->qty, 100);
            if ($targetQty < 1) {
                continue;
            }

            if ($existingCart) {
                $newQuantity = min($existingCart->quantity + $targetQty, (int) $product->qty, 100);
                if ($newQuantity <= $existingCart->quantity) {
                    continue;
                }

                $existingCart->update(['quantity' => $newQuantity]);
            } else {
                Cart::query()->create([
                    'user_id' => Auth::id(),
                    'product_id' => $product->id,
                    'quantity' => $targetQty,
                ]);
            }

            $addedItems++;
        }

        if ($addedItems === 0) {
            return redirect()->route('cart.index')
                ->with('warning', 'Не удалось добавить товары: нет доступных позиций.');
        }

        return redirect()->route('cart.index')
            ->with('success', "Добавлено позиций в корзину: {$addedItems}.");
    }

    public function cancelOrder(Order $order): RedirectResponse
    {
        abort_unless($order->user_id === Auth::id(), 403);

        if (! in_array($order->order_status, ['pending', 'processing'], true)) {
            return redirect()->back()
                ->with('warning', 'Этот заказ уже нельзя отменить.');
        }

        DB::transaction(function () use ($order): void {
            /** @var \Illuminate\Database\Eloquent\Collection<int, OrderProduct> $orderProducts */
            $orderProducts = OrderProduct::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->get();

            foreach ($orderProducts as $orderProduct) {
                Product::query()
                    ->where('id', $orderProduct->product_id)
                    ->lockForUpdate()
                    ->increment('qty', $orderProduct->quantity);
            }

            $order->update([
                'order_status' => 'cancelled',
            ]);
        });

        return redirect()->back()
            ->with('success', 'Заказ успешно отменён.');
    }

    public function downloadInvoice(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $order->load(['products.product:id,name', 'user:id,name']);

        $pdf = Pdf::loadView('invoices.order-invoice', [
            'order' => $order,
            'title' => 'Чек #'.$order->invoice_id,
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
