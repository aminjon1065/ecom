<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

it('keeps in-stock products above out-of-stock products for price sort', function () {
    $category = createCategory('Audio');
    $brand = createBrand('Acme Audio');

    $inStock = createProduct([
        'name' => 'In stock product',
        'slug' => 'in-stock-product',
        'code' => 1101,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'price' => 200,
        'qty' => 2,
    ]);

    $outOfStock = createProduct([
        'name' => 'Out of stock product',
        'slug' => 'out-of-stock-product',
        'code' => 1102,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'price' => 10,
        'qty' => 0,
    ]);

    $response = $this->get(route('products.index', [
        'category' => (string) $category->id,
        'brand' => (string) $brand->id,
        'sort' => 'price_asc',
    ]));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/products/index')
        ->has('products', 2)
        ->where('products.0.id', $inStock->id)
        ->where('products.1.id', $outOfStock->id)
    );
});

it('sorts products by popularity using reviews count', function () {
    $category = createCategory('Popular category');
    $brand = createBrand('Popular brand');

    $mostReviewed = createProduct([
        'name' => 'Most reviewed',
        'slug' => 'most-reviewed',
        'code' => 1201,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
    ]);

    $lessReviewed = createProduct([
        'name' => 'Less reviewed',
        'slug' => 'less-reviewed',
        'code' => 1202,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
    ]);

    $withoutReviews = createProduct([
        'name' => 'Without reviews',
        'slug' => 'without-reviews',
        'code' => 1203,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
    ]);

    createReview($mostReviewed);
    createReview($mostReviewed);
    createReview($mostReviewed);
    createReview($lessReviewed);

    $response = $this->get(route('products.index', [
        'category' => (string) $category->id,
        'brand' => (string) $brand->id,
        'sort' => 'popular',
    ]));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/products/index')
        ->where('products.0.id', $mostReviewed->id)
        ->where('products.1.id', $lessReviewed->id)
        ->where('products.2.id', $withoutReviews->id)
    );
});

it('keeps filters when paginating products', function () {
    $category = createCategory('Paged category');
    $brand = createBrand('Paged brand');

    for ($index = 1; $index <= 13; $index++) {
        createProduct([
            'name' => "Filter keyword {$index}",
            'slug' => "filter-keyword-{$index}",
            'code' => 1300 + $index,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 50 + $index,
            'qty' => 10,
        ]);
    }

    createProduct([
        'name' => 'Another unrelated',
        'slug' => 'another-unrelated',
        'code' => 1999,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'price' => 10,
        'qty' => 10,
    ]);

    $response = $this->get(route('products.index', [
        'search' => 'Filter keyword',
        'category' => (string) $category->id,
        'brand' => (string) $brand->id,
        'sort' => 'latest',
        'page' => 2,
    ]));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/products/index')
        ->where('filters.search', 'Filter keyword')
        ->where('filters.category', (string) $category->id)
        ->where('filters.brand', (string) $brand->id)
        ->where('filters.sort', 'latest')
        ->where('productsMeta.current_page', 2)
        ->where('productsMeta.total', 13)
        ->has('products', 1)
    );
});

it('does not fail product listing search for product with null product type', function () {
    $category = createCategory('Legacy category');
    $brand = createBrand('Legacy brand');

    $legacyProduct = createProduct([
        'name' => 'Legacy Search Product',
        'slug' => 'legacy-search-product',
        'code' => 1401,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'product_type' => null,
    ]);

    $response = $this->get(route('products.index', [
        'search' => 'Legacy Search',
    ]));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/products/index')
        ->has('products', 1)
        ->where('products.0.id', $legacyProduct->id)
    );
});

it('does not fail product listing for product with legacy new arrival product type', function () {
    $category = createCategory('Legacy enum category');
    $brand = createBrand('Legacy enum brand');

    DB::table('products')->insert([
        'name' => 'Legacy New Arrival Product',
        'code' => 1451,
        'slug' => 'legacy-new-arrival-product',
        'thumb_image' => 'products/thumb.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 3,
        'short_description' => 'Short description',
        'long_description' => 'Long description',
        'price' => 99,
        'product_type' => 'new_arrival',
        'status' => true,
        'is_approved' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->get(route('products.index', [
        'category' => (string) $category->id,
    ]));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/products/index')
        ->where('productsMeta.total', 1)
        ->has('products', 1)
        ->where('products.0.slug', 'legacy-new-arrival-product')
    );
});

it('validates max price is not lower than min price on product listing filters', function () {
    $response = $this->get(route('products.index', [
        'min_price' => '100',
        'max_price' => '20',
    ]));

    $response->assertSessionHasErrors('max_price');
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

function createReview(Product $product): ProductReview
{
    return ProductReview::create([
        'product_id' => $product->id,
        'user_id' => User::factory()->create()->id,
        'review' => 'Review',
        'rating' => 5,
        'status' => true,
    ]);
}
