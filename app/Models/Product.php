<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int $code
 * @property string $slug
 * @property string $thumb_image
 * @property int|null $vendor_id
 * @property int $category_id
 * @property int|null $sub_category_id
 * @property int|null $child_category_id
 * @property int $brand_id
 * @property int $qty
 * @property string $short_description
 * @property string $long_description
 * @property string|null $video_link
 * @property string|null $sku
 * @property numeric $price
 * @property float|null $cost_price
 * @property numeric|null $offer_price
 * @property \Illuminate\Support\Carbon|null $offer_start_date
 * @property \Illuminate\Support\Carbon|null $offer_end_date
 * @property string|null $product_type
 * @property bool $status
 * @property bool $is_approved
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $first_source_link
 * @property string|null $second_source_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Brand $brand
 * @property-read \App\Models\Category $category
 * @property-read \App\Models\ChildCategory|null $childCategory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductImageGallery> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductReview> $reviews
 * @property-read int|null $reviews_count
 * @property-read \App\Models\SubCategory|null $subCategory
 * @property-read \App\Models\Vendor|null $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereChildCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCostPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereFirstSourceLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereLongDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereOfferEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereOfferPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereOfferStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSecondSourceLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSeoDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSeoTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSubCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereThumbImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereVideoLink($value)
 * @mixin \Eloquent
 */
class Product extends Model
{

    protected $fillable = [
        'name',
        'code',
        'slug',
        'thumb_image',
        'vendor_id',
        'category_id',
        'sub_category_id',
        'child_category_id',
        'brand_id',
        'qty',
        'short_description',
        'long_description',
        'video_link',
        'sku',
        'price',
        'cost_price',
        'offer_price',
        'offer_start_date',
        'offer_end_date',
        'product_type',
        'status',
        'is_approved',
        'seo_title',
        'seo_description',
        'first_source_link',
        'second_source_link'
    ];

    protected $casts = [
        'price' => 'float',
        'cost_price' => 'float',
        'offer_price' => 'float',
        'qty' => 'integer',
        'status' => 'boolean',
        'is_approved' => 'boolean',
        'offer_start_date' => 'date',
        'offer_end_date' => 'date',
    ];



    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function childCategory(): BelongsTo
    {
        return $this->belongsTo(ChildCategory::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImageGallery::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

}
