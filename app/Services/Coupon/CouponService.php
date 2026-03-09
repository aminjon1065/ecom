<?php

namespace App\Services\Coupon;

use App\DTO\CouponResult;
use App\Enums\CouponType;
use App\Models\Coupons;

class CouponService
{
    public function validateForSubtotal(
        string $code,
        float $subtotal,
        int $userId,
    ): CouponResult {
        $normalizedCode = trim($code);

        if ($normalizedCode === '') {
            return CouponResult::invalid('Введите код купона.');
        }

        $coupon = Coupons::query()
            ->where('code', $normalizedCode)
            ->first();

        if (! $coupon || ! $coupon->is_active) {
            return CouponResult::invalid('Недействительный или просроченный купон.');
        }

        if ($coupon->starts_at !== null && $coupon->starts_at->isFuture()) {
            return CouponResult::invalid('Купон ещё не активен.');
        }

        if ($coupon->ends_at !== null && $coupon->ends_at->isPast()) {
            return CouponResult::invalid('Срок действия купона истёк.');
        }

        if (
            $coupon->usage_limit !== null
            && $coupon->usage_limit > 0
            && $coupon->total_used >= $coupon->usage_limit
        ) {
            return CouponResult::invalid('Купон уже использован максимальное количество раз.');
        }

        if ($coupon->quantity !== null && $coupon->quantity <= 0) {
            return CouponResult::invalid('Купон больше недоступен.');
        }

        $minimumSubtotal = (float) ($coupon->min_subtotal ?? 0);
        if ($minimumSubtotal > 0 && $subtotal < $minimumSubtotal) {
            return CouponResult::invalid("Минимальная сумма заказа для этого купона: {$minimumSubtotal}.");
        }

        $userUsageCount = $coupon->usages()
            ->where('user_id', $userId)
            ->count();

        if ($coupon->first_order_only && $userUsageCount > 0) {
            return CouponResult::invalid('Купон доступен только для первого заказа.');
        }

        if ($coupon->usage_per_user !== null && $coupon->usage_per_user > 0 && $userUsageCount >= $coupon->usage_per_user) {
            return CouponResult::invalid('Вы уже использовали этот купон максимальное количество раз.');
        }

        $type = CouponType::tryFrom($coupon->discount_type ?? '');

        if (! $type) {
            return CouponResult::invalid('Неверный тип скидки для купона.');
        }

        $discountValue = (float) $coupon->discount;
        $discountAmount = match ($type) {
            CouponType::Percent => round(($subtotal * $discountValue) / 100, 2),
            CouponType::Fixed => round($discountValue, 2),
        };

        $discountAmount = min($discountAmount, $subtotal);

        if ($discountAmount <= 0) {
            return CouponResult::invalid('Скидка по купону должна быть больше нуля.');
        }

        return CouponResult::valid(
            coupon: $coupon,
            type: $type,
            discountValue: $discountValue,
            discountAmount: $discountAmount,
        );
    }

    public function consume(Coupons $coupon, int $userId, int $orderId): void
    {
        $coupon->increment('total_used');

        if ($coupon->quantity !== null && $coupon->quantity > 0) {
            $coupon->decrement('quantity');
        }

        $coupon->usages()->create([
            'user_id' => $userId,
            'order_id' => $orderId,
            'used_at' => now(),
        ]);
    }
}
