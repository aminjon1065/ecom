<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\PopularSearchQuery;
use App\Models\Product;
use App\Models\ProductViewEvent;
use Illuminate\Support\Str;

it('returns product suggestions with category and brand for search api', function () {
    $category = Category::query()->create([
        'name' => 'Search Category '.Str::random(4),
        'slug' => 'search-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Search Brand '.Str::random(4),
        'slug' => 'search-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    Product::query()->create([
        'name' => 'Searchable Headphones',
        'code' => 801001,
        'slug' => 'searchable-headphones',
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 3,
        'short_description' => 'Noise cancelling',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 100,
        'status' => true,
        'is_approved' => true,
    ]);

    $response = $this->getJson(route('api.search', ['q' => 'head']));

    $response->assertSuccessful();
    $response->assertJsonCount(1);
    $response->assertJsonPath('0.category.id', $category->id);
    $response->assertJsonPath('0.brand.id', $brand->id);
});

it('returns empty list for too short query in search api', function () {
    $response = $this->getJson(route('api.search', ['q' => 'h']));

    $response->assertSuccessful();
    $response->assertExactJson([]);
});

it('returns popular queries from database for search suggestions', function () {
    $category = Category::query()->create([
        'name' => 'Popular Search Category '.Str::random(4),
        'slug' => 'popular-search-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Popular Search Brand '.Str::random(4),
        'slug' => 'popular-search-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    $phone = Product::query()->create([
        'name' => 'Popular Phone',
        'code' => 801010,
        'slug' => 'popular-phone',
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 8,
        'short_description' => 'Popular phone',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 200,
        'status' => true,
        'is_approved' => true,
    ]);

    $headphones = Product::query()->create([
        'name' => 'Popular Headphones',
        'code' => 801011,
        'slug' => 'popular-headphones',
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 8,
        'short_description' => 'Popular headphones',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 120,
        'status' => true,
        'is_approved' => true,
    ]);

    foreach (range(1, 4) as $index) {
        ProductViewEvent::query()->create([
            'product_id' => $phone->id,
            'user_id' => null,
            'session_id' => 'popular-phone-'.$index,
            'viewed_at' => now()->subMinutes($index),
        ]);
    }

    foreach (range(1, 2) as $index) {
        ProductViewEvent::query()->create([
            'product_id' => $headphones->id,
            'user_id' => null,
            'session_id' => 'popular-headphones-'.$index,
            'viewed_at' => now()->subMinutes($index),
        ]);
    }

    $response = $this->getJson(route('api.search.popular'));

    $response->assertSuccessful();
    $response->assertJsonPath('0', 'Popular Phone');
    $response->assertJsonPath('1', 'Popular Headphones');
});

it('prefers active admin popular queries over auto-generated suggestions', function () {
    PopularSearchQuery::query()->create([
        'query' => 'Admin Query One',
        'priority' => 100,
        'is_active' => true,
    ]);

    PopularSearchQuery::query()->create([
        'query' => 'Admin Query Two',
        'priority' => 50,
        'is_active' => true,
    ]);

    $response = $this->getJson(route('api.search.popular'));

    $response->assertSuccessful();
    $response->assertExactJson([
        'Admin Query One',
        'Admin Query Two',
    ]);
});
