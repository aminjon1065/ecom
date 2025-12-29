<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $image
 * @property int $product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImageGallery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImageGallery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImageGallery query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImageGallery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImageGallery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImageGallery whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImageGallery whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImageGallery whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductImageGallery extends Model
{
    protected $fillable = [
        'image',
        'product_id'
    ];
    protected $casts = [
        'product_id' => 'integer'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
