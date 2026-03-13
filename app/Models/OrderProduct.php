<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int|null $product_variant_item_id
 * @property string|null $variant_name
 * @property int $quantity
 * @property float $unit_price
 * @property float $discount_amount
 * @property float $line_total
 * @property string $product_name
 * @property string|null $product_sku
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\ProductVariantItem|null $variant
 *
 * @mixin \Eloquent
 */
class OrderProduct extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_item_id',
        'variant_name',
        'quantity',
        'unit_price',
        'discount_amount',
        'line_total',
        'product_name',
        'product_sku',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'double',
        'discount_amount' => 'double',
        'line_total' => 'double',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariantItem::class, 'product_variant_item_id');
    }
}
