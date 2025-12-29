<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $end_date
 * @property int $product_id
 * @property bool $status
 * @property bool $show_at_main
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FlashSale newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FlashSale newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FlashSale query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FlashSale whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FlashSale whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FlashSale whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FlashSale whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FlashSale whereShowAtMain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FlashSale whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FlashSale whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FlashSale extends Model
{
    protected $fillable = [
        'end_date',
        'product_id',
        'status',
        'show_at_main'
    ];

    protected $casts = [
        'status' => 'boolean',
        'show_at_main' => 'boolean'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
