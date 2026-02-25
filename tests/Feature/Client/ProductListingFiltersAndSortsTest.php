<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

it('applies filters and sorting on the client products page', function () {
    $electronics = createCategory('Electronics');
    $furniture = createCategory('Furniture');

    $acme = createBrand('Acme');
    $globex = createBrand('Globex');

    $expensiveMatch = createProduct([
        'name' => 'Acme Speaker Pro',
        'slug' => 'acme-speaker-pro',
        'code' => 1001,
        'category_id' => $electronics->id,
        'brand_id' => $acme->id,
        'price' => 80,
        'qty' => 10,
        'status' => true,
        'is_approved' => true,
    ]);

    $cheaperMatch = createProduct([
        'name' => 'Acme Speaker Mini',
        'slug' => 'acme-speaker-mini',
        'code' => 1002,
        'category_id' => $electronics->id,
        'brand_id' => $acme->id,
        'price' => 30,
        'qty' => 5,
        'status' => true,
        'is_approved' => true,
    ]);

    createProduct([
        'name' => 'Wrong Brand',
        'slug' => 'wrong-brand',
        'code' => 1003,
        'category_id' => $electronics->id,
        'brand_id' => $globex->id,
        'price' => 70,
        'status' => true,
        'is_approved' => true,
    ]);

    createProduct([
        'name' => 'Wrong Category',
        'slug' => 'wrong-category',
        'code' => 1004,
        'category_id' => $furniture->id,
        'brand_id' => $acme->id,
        'price' => 60,
        'status' => true,
        'is_approved' => true,
    ]);

    createProduct([
        'name' => 'Too Cheap',
        'slug' => 'too-cheap',
        'code' => 1005,
        'category_id' => $electronics->id,
        'brand_id' => $acme->id,
        'price' => 10,
        'status' => true,
        'is_approved' => true,
    ]);

    createProduct([
        'name' => 'Hidden Product',
        'slug' => 'hidden-product',
        'code' => 1006,
        'category_id' => $electronics->id,
        'brand_id' => $acme->id,
        'price' => 75,
        'status' => false,
        'is_approved' => true,
    ]);

    createProduct([
        'name' => 'Not Approved Product',
        'slug' => 'not-approved-product',
        'code' => 1007,
        'category_id' => $electronics->id,
        'brand_id' => $acme->id,
        'price' => 75,
        'status' => true,
        'is_approved' => false,
    ]);

    $response = $this->get(route('products.index', [
        'category' => (string) $electronics->id,
        'brand' => (string) $acme->id,
        'min_price' => '20',
        'max_price' => '90',
        'sort' => 'price_desc',
    ]));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/products/index')
        ->where('filters.category', (string) $electronics->id)
        ->where('filters.brand', (string) $acme->id)
        ->where('filters.min_price', '20')
        ->where('filters.max_price', '90')
        ->where('filters.sort', 'price_desc')
        ->where('productsMeta.total', 2)
        ->has('products', 2)
        ->where('products.0.id', $expensiveMatch->id)
        ->where('products.1.id', $cheaperMatch->id)
    );
});

function createCategory(string $name): Category
{
    return Category::create([
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);
}

function createBrand(string $name): Brand
{
    return Brand::create([
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
        'logo' => 'brands/logo.png',
        'status' => true,
        'is_featured' => true,
    ]);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createProduct(array $attributes): Product
{
    static $sequence = 2000;

    $sequence++;

    return Product::create(array_merge([
        'name' => 'Product '.$sequence,
        'code' => $sequence,
        'slug' => 'product-'.$sequence,
        'thumb_image' => 'products/thumb.png',
        'category_id' => createCategory('Category '.$sequence)->id,
        'brand_id' => createBrand('Brand '.$sequence)->id,
        'qty' => 1,
        'short_description' => 'Short description',
        'long_description' => 'Long description',
        'price' => 50,
        'status' => true,
        'is_approved' => true,
    ], $attributes));
}
