<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\ApplyCouponRequest;
use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Models\Cart;
use App\Models\ShippingRules;
use App\Models\UserAddress;
use App\Services\Checkout\CheckoutService;
use App\Services\Coupon\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CouponService $couponService,
        private readonly CheckoutService $checkoutService,
    ) {}

    public function index(): Response
    {
        $cartItems = Cart::where('user_id', Auth::id())
            ->with(['product:id,name,thumb_image,price,offer_price,offer_start_date,offer_end_date,qty'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $addresses = UserAddress::where('user_id', Auth::id())->get();
        $shippingRules = ShippingRules::where('status', true)->get();

        return Inertia::render('client/checkout', [
            'cartItems' => $cartItems,
            'addresses' => $addresses,
            'shippingRules' => $shippingRules,
        ]);
    }

    public function applyCoupon(ApplyCouponRequest $request): RedirectResponse
    {
        $startedAt = microtime(true);
        $requestId = $request->header('X-Request-Id', (string) Str::uuid());
        $userId = Auth::id();

        $cartItems = $this->checkoutService->cartItemsForUser($userId, [
            'id',
            'price',
            'offer_price',
            'offer_start_date',
            'offer_end_date',
        ]);

        if ($cartItems->isEmpty()) {
            Log::warning('checkout.coupon.apply', [
                'event' => 'checkout.coupon.apply',
                'status' => 'cart_empty',
                'user_id' => $userId,
                'request_id' => $requestId,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            return redirect()->route('cart.index');
        }

        $subtotal = $this->checkoutService->calculateSubtotal($cartItems);
        $result = $this->couponService->validateForSubtotal(
            code: (string) $request->validated('code'),
            subtotal: $subtotal,
            userId: $userId,
        );

        if (! $result->isValid || ! $result->coupon || ! $result->type) {
            Log::warning('checkout.coupon.apply', [
                'event' => 'checkout.coupon.apply',
                'status' => 'invalid',
                'user_id' => $userId,
                'request_id' => $requestId,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            return back()->withErrors(['code' => $result->message ?? 'Купон не может быть применён.']);
        }

        Log::info('checkout.coupon.apply', [
            'event' => 'checkout.coupon.apply',
            'status' => 'success',
            'coupon_code' => $result->coupon->code,
            'user_id' => $userId,
            'request_id' => $requestId,
            'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
        ]);

        return back()->with('appliedCoupon', [
            'code' => $result->coupon->code,
            'discount_type' => $result->type->value,
            'discount' => $result->discountValue,
            'discount_amount' => $result->discountAmount,
        ]);
    }

    public function removeCoupon(): RedirectResponse
    {
        return back()->with('appliedCoupon', null);
    }

    public function store(StoreCheckoutRequest $request): RedirectResponse
    {
        $result = $this->checkoutService->placeOrder(
            validated: $request->validated(),
            userId: Auth::id(),
            requestId: $request->header('X-Request-Id', (string) Str::uuid()),
            startedAt: microtime(true),
        );

        return match ($result->status) {
            'cart_empty' => redirect()->route('cart.index'),
            'invalid' => back()->withErrors([
                $result->field ?? 'checkout' => $result->message ?? 'Оформление заказа недоступно.',
            ]),
            'idempotent', 'success' => redirect()->route('account.orders.show', $result->order),
            default => throw new \RuntimeException('Unknown checkout result status.'),
        };
    }
}
