<?php

namespace App\DTO;

use App\Enums\CouponType;
use App\Models\Coupons;

class CouponResult
{
    public function __construct(
        public bool $isValid,
        public ?string $message = null,
        public ?Coupons $coupon = null,
        public ?CouponType $type = null,
        public float $discountValue = 0.0,
        public float $discountAmount = 0.0,
    ) {}

    public static function invalid(string $message): self
    {
        return new self(isValid: false, message: $message);
    }

    public static function valid(
        Coupons $coupon,
        CouponType $type,
        float $discountValue,
        float $discountAmount,
    ): self {
        return new self(
            isValid: true,
            coupon: $coupon,
            type: $type,
            discountValue: $discountValue,
            discountAmount: $discountAmount,
        );
    }
}
