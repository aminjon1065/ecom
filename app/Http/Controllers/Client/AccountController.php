<?php

namespace App\Http\Controllers\Client;

use App\Actions\Account\BuildDashboardDataAction;
use App\Actions\Account\BuildOrdersPageDataAction;
use App\Actions\Account\CancelOrderAction;
use App\Actions\Account\RepeatOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\AccountOrderIndexRequest;
use App\Http\Requests\Client\UpdateAccountPasswordRequest;
use App\Http\Requests\Client\UpdateAccountProfileRequest;
use App\Models\Order;
use App\Models\UserAddress;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __construct(
        private readonly BuildDashboardDataAction $buildDashboardDataAction,
        private readonly BuildOrdersPageDataAction $buildOrdersPageDataAction,
        private readonly RepeatOrderAction $repeatOrderAction,
        private readonly CancelOrderAction $cancelOrderAction,
    ) {}

    public function dashboard(AccountOrderIndexRequest $request): Response
    {
        $user = Auth::user();
        $filters = $request->validated();
        $pageData = $this->buildDashboardDataAction->handle($user, $filters);

        return Inertia::render('client/account/dashboard', $pageData);
    }

    public function orders(AccountOrderIndexRequest $request): Response
    {
        $filters = $request->validated();
        $pageData = $this->buildOrdersPageDataAction->handle((int) Auth::id(), $filters);

        return Inertia::render('client/account/orders', $pageData);
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

        $addedItems = $this->repeatOrderAction->handle($order, (int) Auth::id());

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

        if (! $this->cancelOrderAction->canCancel($order)) {
            return redirect()->back()
                ->with('warning', 'Этот заказ уже нельзя отменить.');
        }

        $this->cancelOrderAction->handle($order);

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
        $user = Auth::user();

        $isSocialOnly = ($user->telegram_id || $user->google_id)
            && str_ends_with($user->email, '@telegram.local');

        return Inertia::render('client/account/profile', [
            'isSocialOnly' => $isSocialOnly,
        ]);
    }

    public function updateProfile(UpdateAccountProfileRequest $request): RedirectResponse
    {
        Auth::user()->update($request->validated());

        return redirect()->back();
    }

    public function updatePassword(UpdateAccountPasswordRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $user->forceFill([
            'password' => Hash::make($request->validated('password')),
        ])->save();

        return redirect()->back()->with('success', 'Пароль успешно изменён.');
    }
}
