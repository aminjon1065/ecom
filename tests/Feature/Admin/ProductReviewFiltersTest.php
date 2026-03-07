<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

it('filters admin review list by verified purchase', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $product = createFilterableReviewProduct(8801);

    $verifiedReview = ProductReview::query()->create([
        'product_id' => $product->id,
        'user_id' => User::factory()->create()->id,
        'review' => 'Verified review',
        'rating' => 5,
        'status' => true,
        'verified_purchase' => true,
    ]);

    ProductReview::query()->create([
        'product_id' => $product->id,
        'user_id' => User::factory()->create()->id,
        'review' => 'Unverified review',
        'rating' => 4,
        'status' => true,
        'verified_purchase' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.review.index', [
        'verified_purchase' => '1',
    ]));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/review/index')
        ->where('filters.verified_purchase', '1')
        ->has('reviews.data', 1)
        ->where('reviews.data.0.id', $verifiedReview->id)
        ->where('reviews.data.0.verified_purchase', true)
    );
});

function createFilterableReviewProduct(int $code): Product
{
    $category = Category::query()->create([
        'name' => 'Review Filter Category '.Str::random(4),
        'slug' => 'review-filter-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Review Filter Brand '.Str::random(4),
        'slug' => 'review-filter-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Review Filter Product '.$code,
        'code' => $code,
        'slug' => 'review-filter-product-'.$code,
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 5,
        'short_description' => 'Short',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 100,
        'status' => true,
        'is_approved' => true,
    ]);
}
