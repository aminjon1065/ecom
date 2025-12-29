<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property string $name
 * @property float $price
 * @property bool $is_default
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantItem whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariantItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductVariantItem extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'price',
        'is_default',
        'status'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'status' => 'boolean'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
