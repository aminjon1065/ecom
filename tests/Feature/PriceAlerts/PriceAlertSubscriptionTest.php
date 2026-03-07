<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceAlert;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

it('subscribes user to product price alert and avoids duplicates', function () {
    $user = User::factory()->create();
    $product = makeAlertProduct(7001, 120);

    $this->actingAs($user)
        ->post(route('price-alerts.store', $product))
        ->assertRedirect();

    expect(PriceAlert::query()->count())->toBe(1)
        ->and(PriceAlert::query()->firstOrFail()->target_price)->toBe(120.0);

    $this->actingAs($user)
        ->post(route('price-alerts.store', $product))
        ->assertRedirect();

    expect(PriceAlert::query()->count())->toBe(1);
});

it('unsubscribes user from product price alert', function () {
    $user = User::factory()->create();
    $product = makeAlertProduct(7002, 220);

    PriceAlert::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'target_price' => 220,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->delete(route('price-alerts.destroy', $product))
        ->assertRedirect();

    expect(PriceAlert::query()->count())->toBe(0);
});

function makeAlertProduct(int $code, float $price): Product
{
    $category = Category::query()->create([
        'name' => 'Alert Category '.Str::random(4),
        'slug' => 'alert-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Alert Brand '.Str::random(4),
        'slug' => 'alert-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Alert Product '.$code,
        'code' => $code,
        'slug' => 'alert-product-'.$code,
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 20,
        'short_description' => 'Short',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => $price,
        'status' => true,
        'is_approved' => true,
    ]);
}
