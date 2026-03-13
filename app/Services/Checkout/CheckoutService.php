<?php

namespace App\Services\Checkout;

use App\DTO\CheckoutOrderResult;
use App\Enums\OrderStatus;
use App\Jobs\SendOrderPlacedNotificationsJob;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ShippingRules;
use App\Services\Coupon\CouponService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    public function __construct(
        private readonly CouponService $couponService,
    ) {}

    /**
     * @param  array<int, string>  $productColumns
     * @return Collection<int, Cart>
     */
    public function cartItemsForUser(int $userId, array $productColumns = ['*']): Collection
    {
        return Cart::query()
            ->where('user_id', $userId)
            ->with([
                'product' => fn ($query) => $query->select($productColumns),
                'variant:id,name,price',
            ])
            ->get();
    }

    /**
     * @param  Collection<int, Cart>  $cartItems
     * @param  Collection<int, Product>|null  $products
     */
    public function calculateSubtotal(Collection $cartItems, ?Collection $products = null): float
    {
        return (float) $cartItems->sum(function (Cart $item) use ($products): float {
            $product = $products?->get($item->product_id) ?? $item->product;

            if (! $product instanceof Product) {
                return 0.0;
            }

            return $product->effectivePrice() * $item->quantity;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function placeOrder(array $validated, int $userId, string $requestId, float $startedAt): CheckoutOrderResult
    {
        $idempotencyKey = (string) $validated['idempotency_key'];

        $existingOrder = Order::query()
            ->where('user_id', $userId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existingOrder) {
            $this->logInfo('idempotent_hit', $userId, $requestId, $startedAt, [
                'order_id' => $existingOrder->id,
            ]);

            return CheckoutOrderResult::idempotent($existingOrder);
        }

        $cartItems = $this->cartItemsForUser($userId);

        if ($cartItems->isEmpty()) {
            $this->logWarning('cart_empty', $userId, $requestId, $startedAt);

            return CheckoutOrderResult::cartEmpty();
        }

        try {
            return DB::transaction(function () use ($validated, $cartItems, $idempotencyKey, $requestId, $startedAt, $userId): CheckoutOrderResult {
                $lockedProducts = Product::query()
                    ->whereIn('id', $cartItems->pluck('product_id')->all())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($cartItems as $item) {
                    /** @var Product|null $lockedProduct */
                    $lockedProduct = $lockedProducts->get($item->product_id);

                    if (! $lockedProduct || $lockedProduct->qty < $item->quantity) {
                        $this->logWarning('stock_insufficient', $userId, $requestId, $startedAt, [
                            'product_id' => $item->product_id,
                        ]);

                        return CheckoutOrderResult::invalid(
                            field: 'checkout',
                            message: "Недостаточно товара \"{$item->product?->name}\" на складе.",
                        );
                    }
                }

                $existingOrderInTransaction = Order::query()
                    ->where('user_id', $userId)
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existingOrderInTransaction) {
                    $this->logInfo('idempotent_hit_in_tx', $userId, $requestId, $startedAt, [
                        'order_id' => $existingOrderInTransaction->id,
                    ]);

                    return CheckoutOrderResult::idempotent($existingOrderInTransaction);
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
                        userId: $userId,
                    );

                    if (! $couponResult->isValid || ! $couponResult->coupon) {
                        $this->logWarning('coupon_invalid', $userId, $requestId, $startedAt, [
                            'coupon_code' => $validated['coupon_code'],
                        ]);

                        return CheckoutOrderResult::invalid(
                            field: 'coupon_code',
                            message: $couponResult->message ?? 'Купон не может быть применён.',
                        );
                    }

                    $discount = $couponResult->discountAmount;
                    $couponCode = $couponResult->coupon->code;
                    $appliedCoupon = $couponResult->coupon;
                }

                $grandTotal = max(0, $subtotal + $shippingCost - $discount);

                $order = Order::query()->create([
                    'invoice_id' => random_int(100000, 999999),
                    'transaction_id' => 'TXN-'.strtoupper(bin2hex(random_bytes(8))),
                    'idempotency_key' => $idempotencyKey,
                    'user_id' => $userId,
                    'subtotal' => $subtotal,
                    'discount_total' => $discount,
                    'shipping_total' => $shippingCost,
                    'grand_total' => $grandTotal,
                    'product_quantity' => $cartItems->sum('quantity'),
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => false,
                    'coupon_code' => $couponCode,
                    'order_status' => OrderStatus::Pending,
                ]);

                $distributedDiscount = 0.0;
                $itemsCount = $cartItems->count();

                foreach ($cartItems as $index => $item) {
                    /** @var Product $lockedProduct */
                    $lockedProduct = $lockedProducts->get($item->product_id);

                    $unitPrice = $lockedProduct->effectivePrice();
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

                    OrderProduct::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'product_variant_item_id' => $item->product_variant_item_id,
                        'variant_name' => $item->variant?->name,
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

                Cart::query()->where('user_id', $userId)->delete();

                SendOrderPlacedNotificationsJob::dispatch($order->id)->afterCommit();

                $this->logInfo('success', $userId, $requestId, $startedAt, [
                    'order_id' => $order->id,
                ]);

                return CheckoutOrderResult::success($order);
            });
        } catch (QueryException $exception) {
            $existingOrderAfterConflict = Order::query()
                ->where('user_id', $userId)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingOrderAfterConflict) {
                $this->logInfo('idempotent_conflict_recovered', $userId, $requestId, $startedAt, [
                    'order_id' => $existingOrderAfterConflict->id,
                ]);

                return CheckoutOrderResult::idempotent($existingOrderAfterConflict);
            }

            Log::error('checkout.store', [
                'event' => 'checkout.store',
                'status' => 'failed',
                'user_id' => $userId,
                'request_id' => $requestId,
                'duration_ms' => $this->durationMs($startedAt),
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
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

    /**
     * @param  array<string, mixed>  $context
     */
    private function logInfo(string $status, int $userId, string $requestId, float $startedAt, array $context = []): void
    {
        Log::info('checkout.store', [
            'event' => 'checkout.store',
            'status' => $status,
            'user_id' => $userId,
            'request_id' => $requestId,
            'duration_ms' => $this->durationMs($startedAt),
            ...$context,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function logWarning(string $status, int $userId, string $requestId, float $startedAt, array $context = []): void
    {
        Log::warning('checkout.store', [
            'event' => 'checkout.store',
            'status' => $status,
            'user_id' => $userId,
            'request_id' => $requestId,
            'duration_ms' => $this->durationMs($startedAt),
            ...$context,
        ]);
    }

    private function durationMs(float $startedAt): int
    {
        return (int) ((microtime(true) - $startedAt) * 1000);
    }
}
