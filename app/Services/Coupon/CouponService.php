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

        if (! $coupon || ! $coupon->status) {
            return CouponResult::invalid('Недействительный или просроченный купон.');
        }

        if (isset($coupon->is_active) && $coupon->is_active === false) {
            return CouponResult::invalid('Недействительный или просроченный купон.');
        }

        $startsAt = $coupon->starts_at ?? $coupon->start_date;
        $endsAt = $coupon->ends_at ?? $coupon->end_date;

        if ($startsAt !== null && $startsAt->isFuture()) {
            return CouponResult::invalid('Купон ещё не активен.');
        }

        if ($endsAt !== null && $endsAt->isPast()) {
            return CouponResult::invalid('Срок действия купона истёк.');
        }

        $usageLimit = $coupon->usage_limit ?? $coupon->max_use;
        if (
            $usageLimit !== null
            && $usageLimit > 0
            && $coupon->total_used >= $usageLimit
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

        $usagePerUser = $coupon->usage_per_user;
        if ($usagePerUser !== null && $usagePerUser > 0 && $userUsageCount >= $usagePerUser) {
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
