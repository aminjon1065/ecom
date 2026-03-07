<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $quantity
 * @property int $max_use
 * @property string $start_date
 * @property string $end_date
 * @property string $discount_type
 * @property float $discount
 * @property bool $status
 * @property int $total_used
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons whereMaxUse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons whereTotalUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupons whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Coupons extends Model
{
    protected $fillable = [
        'name',
        'code',
        'quantity',
        'max_use',
        'start_date',
        'end_date',
        'discount_type',
        'discount',
        'status',
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
        'status' => 'boolean',
        'is_active' => 'boolean',
        'first_order_only' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'discount' => 'float',
        'min_subtotal' => 'float',
        'usage_limit' => 'integer',
        'usage_per_user' => 'integer',
        'quantity' => 'integer',
        'max_use' => 'integer',
        'total_used' => 'integer',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class, 'coupon_id');
    }
}
