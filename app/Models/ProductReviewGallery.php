<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_review_id
 * @property string $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProductReview|null $review
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReviewGallery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReviewGallery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReviewGallery query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReviewGallery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReviewGallery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReviewGallery whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReviewGallery whereProductReviewId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReviewGallery whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductReviewGallery extends Model
{
    protected $fillable = [
        'product_review_id',
        'image'
    ];

    protected $casts = [
        'product_review_id' => 'integer'
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(ProductReview::class);
    }
}
