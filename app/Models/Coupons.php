<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $quantity
 * @property string $discount_type
 * @property float $discount
 * @property bool $is_active
 * @property bool $first_order_only
 * @property int $total_used
 * @property \Illuminate\Support\Carbon|null $starts_at
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property int|null $usage_limit
 * @property int|null $usage_per_user
 * @property float|null $min_subtotal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @mixin \Eloquent
 */
class Coupons extends Model
{
    protected $fillable = [
        'name',
        'code',
        'quantity',
        'discount_type',
        'discount',
        'total_used',
        'starts_at',
        'ends_at',
        'usage_limit',
        'usage_per_user',
        'min_subtotal',
        'is_active',
        'first_order_only',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'first_order_only' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'discount' => 'float',
        'min_subtotal' => 'float',
        'usage_limit' => 'integer',
        'usage_per_user' => 'integer',
        'quantity' => 'integer',
        'total_used' => 'integer',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class, 'coupon_id');
    }
}
