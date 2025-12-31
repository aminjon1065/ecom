<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $category_id
 * @property int $sub_category_id
 * @property string $name
 * @property string $slug
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \App\Models\SubCategory $sub_category
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChildCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChildCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChildCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChildCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChildCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChildCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChildCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChildCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChildCategory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChildCategory whereSubCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChildCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChildCategory extends Model
{
    protected $fillable = [
        'category_id',
        'sub_category_id',
        'name',
        'slug',
        'status'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

}
