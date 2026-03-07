<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\ApplyCouponRequest;
use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Jobs\SendOrderPlacedNotificationsJob;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ShippingRules;
use App\Models\UserAddress;
use App\Services\Coupon\CouponService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CouponService $couponService,
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

        $cartItems = Cart::query()
            ->where('user_id', $userId)
            ->with('product:id,price,offer_price,offer_start_date,offer_end_date')
            ->get();

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

        $subtotal = $this->calculateSubtotal($cartItems);
        $result = $this->couponService->validateForSubtotal(
            code: (string) $request->validated('code'),
            subtotal: $subtotal,
            userId: Auth::id(),
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
        $startedAt = microtime(true);
        $requestId = $request->header('X-Request-Id', (string) Str::uuid());
        $userId = Auth::id();
        $validated = $request->validated();
        $idempotencyKey = (string) $validated['idempotency_key'];

        $existingOrder = Order::query()
            ->where('user_id', $userId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existingOrder) {
            Log::info('checkout.store', [
                'event' => 'checkout.store',
                'status' => 'idempotent_hit',
                'order_id' => $existingOrder->id,
                'user_id' => $userId,
                'request_id' => $requestId,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            return redirect()->route('account.orders.show', $existingOrder);
        }

        $cartItems = Cart::query()
            ->where('user_id', $userId)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            Log::warning('checkout.store', [
                'event' => 'checkout.store',
                'status' => 'cart_empty',
                'user_id' => $userId,
                'request_id' => $requestId,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            return redirect()->route('cart.index');
        }

        try {
            return DB::transaction(function () use ($validated, $cartItems, $idempotencyKey, $requestId, $startedAt, $userId) {
                $lockedProducts = Product::query()
                    ->whereIn('id', $cartItems->pluck('product_id')->all())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($cartItems as $item) {
                    /** @var Product|null $lockedProduct */
                    $lockedProduct = $lockedProducts->get($item->product_id);
                    if (! $lockedProduct || $lockedProduct->qty < $item->quantity) {
                        Log::warning('checkout.store', [
                            'event' => 'checkout.store',
                            'status' => 'stock_insufficient',
                            'product_id' => $item->product_id,
                            'user_id' => $userId,
                            'request_id' => $requestId,
                            'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
                        ]);

                        return back()->withErrors([
                            'checkout' => "Недостаточно товара \"{$item->product->name}\" на складе.",
                        ]);
                    }
                }

                $existingOrderInTransaction = Order::query()
                    ->where('user_id', $userId)
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existingOrderInTransaction) {
                    Log::info('checkout.store', [
                        'event' => 'checkout.store',
                        'status' => 'idempotent_hit_in_tx',
                        'order_id' => $existingOrderInTransaction->id,
                        'user_id' => $userId,
                        'request_id' => $requestId,
                        'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
                    ]);

                    return redirect()->route('account.orders.show', $existingOrderInTransaction);
                }

                $subtotal = $this->calculateSubtotal($cartItems, $lockedProducts);
                $shippingCost = $this->resolveShippingCost($validated['shipping_rule_id'] ?? null, $subtotal);

                $discount = 0.0;
                $couponCode = null;
                $appliedCoupon = null;

                if (! empty($validated['coupon_code'])) {
                    $couponResult = $this->couponService->validateForSubtotal(
                        code: (string) $validated['coupon_code'],
                        subtotal: $subtotal,
                        userId: Auth::id(),
                    );

                    if (! $couponResult->isValid || ! $couponResult->coupon) {
                        Log::warning('checkout.store', [
                            'event' => 'checkout.store',
                            'status' => 'coupon_invalid',
                            'coupon_code' => $validated['coupon_code'],
                            'user_id' => $userId,
                            'request_id' => $requestId,
                            'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
                        ]);

                        return back()->withErrors([
                            'coupon_code' => $couponResult->message ?? 'Купон не может быть применён.',
                        ]);
                    }

                    $discount = $couponResult->discountAmount;
                    $couponCode = $couponResult->coupon->code;
                    $appliedCoupon = $couponResult->coupon;
                }

                $grandTotal = max(0, $subtotal + $shippingCost - $discount);

                $order = Order::create([
                    'invoice_id' => mt_rand(100000, 999999),
                    'transaction_id' => 'TXN-'.strtoupper(uniqid()),
                    'idempotency_key' => $idempotencyKey,
                    'user_id' => $userId,
                    'amount' => $grandTotal,
                    'subtotal' => $subtotal,
                    'discount_total' => $discount,
                    'shipping_total' => $shippingCost,
                    'grand_total' => $grandTotal,
                    'product_quantity' => $cartItems->sum('quantity'),
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => false,
                    'coupon' => $couponCode,
                    'coupon_code' => $couponCode,
                    'order_status' => 'pending',
                ]);

                $distributedDiscount = 0.0;
                $itemsCount = $cartItems->count();

                foreach ($cartItems as $index => $item) {
                    /** @var Product $lockedProduct */
                    $lockedProduct = $lockedProducts->get($item->product_id);

                    $unitPrice = $this->getEffectivePrice($lockedProduct);
                    $lineSubtotal = round($unitPrice * $item->quantity, 2);
                    $lineDiscount = 0.0;

                    if ($discount > 0 && $subtotal > 0) {
                        if ($index === $itemsCount - 1) {
                            $lineDiscount = round($discount - $distributedDiscount, 2);
                        } else {
                            $lineDiscount = round(($lineSubtotal / $subtotal) * $discount, 2);
                            $distributedDiscount += $lineDiscount;
                        }
                    }

                    OrderProduct::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $unitPrice,
                        'discount_amount' => $lineDiscount,
                        'line_total' => max(0, $lineSubtotal - $lineDiscount),
                        'product_name' => $lockedProduct->name,
                        'product_sku' => $lockedProduct->sku,
                    ]);

                    $lockedProduct->decrement('qty', $item->quantity);
                }

                if ($appliedCoupon !== null) {
                    $this->couponService->consume($appliedCoupon, $userId, $order->id);
                }

                Cart::where('user_id', $userId)->delete();

                SendOrderPlacedNotificationsJob::dispatch($order->id)->afterCommit();

                Log::info('checkout.store', [
                    'event' => 'checkout.store',
                    'status' => 'success',
                    'order_id' => $order->id,
                    'user_id' => $userId,
                    'request_id' => $requestId,
                    'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
                ]);

                return redirect()->route('account.orders.show', $order->id);
            });
        } catch (QueryException $exception) {
            $existingOrderAfterConflict = Order::query()
                ->where('user_id', $userId)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingOrderAfterConflict) {
                Log::info('checkout.store', [
                    'event' => 'checkout.store',
                    'status' => 'idempotent_conflict_recovered',
                    'order_id' => $existingOrderAfterConflict->id,
                    'user_id' => $userId,
                    'request_id' => $requestId,
                    'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
                ]);

                return redirect()->route('account.orders.show', $existingOrderAfterConflict);
            }

            Log::error('checkout.store', [
                'event' => 'checkout.store',
                'status' => 'failed',
                'user_id' => $userId,
                'request_id' => $requestId,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function calculateSubtotal($cartItems, $lockedProducts = null): float
    {
        return (float) $cartItems->sum(function ($item) use ($lockedProducts) {
            $product = $lockedProducts?->get($item->product_id) ?? $item->product;
            $price = $this->getEffectivePrice($product);

            return $price * $item->quantity;
        });
    }

    private function resolveShippingCost(?int $shippingRuleId, float $subtotal): float
    {
        if (! $shippingRuleId) {
            return 0.0;
        }

        $rule = ShippingRules::query()->find($shippingRuleId);
        if (! $rule) {
            return 0.0;
        }

        return match ($rule->type) {
            'flat' => (float) $rule->cost,
            'free_shipping' => 0.0,
            'min_cost' => $subtotal >= (float) ($rule->min_cost ?? 0)
                ? 0.0
                : (float) $rule->cost,
            default => 0.0,
        };
    }

    private function getEffectivePrice($product): float
    {
        if (
            $product->offer_price &&
            $product->offer_start_date &&
            $product->offer_end_date &&
            now()->between($product->offer_start_date, $product->offer_end_date)
        ) {
            return $product->offer_price;
        }

        return $product->price;
    }
}
