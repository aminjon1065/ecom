<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

it('shows review as unavailable for guests', function () {
    $product = createShowProduct(3101);

    $response = $this->get(route('products.show', $product->slug));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/products/show')
        ->where('isAuthenticated', false)
        ->where('canReviewProduct', false)
    );
});

it('shows review as unavailable for authenticated users without purchase', function () {
    $user = User::factory()->create();
    $product = createShowProduct(3102);

    $response = $this->actingAs($user)->get(route('products.show', $product->slug));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/products/show')
        ->where('isAuthenticated', true)
        ->where('canReviewProduct', false)
    );
});

it('shows review form as available for authenticated users who purchased product', function () {
    $user = User::factory()->create();
    $product = createShowProduct(3103);

    $order = Order::query()->create([
        'invoice_id' => 620001,
        'transaction_id' => 'TXN-SHOW-REVIEW-1',
        'user_id' => $user->id,
        'grand_total' => 100,
        'subtotal' => 100,
        'discount_total' => 0,
        'shipping_total' => 0,
        'grand_total' => 100,
        'product_quantity' => 1,
        'payment_method' => 'cash',
        'payment_status' => false,
        'coupon_code' => null,
        'coupon_code' => null,
        'order_status' => 'delivered',
    ]);

    OrderProduct::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 100,
        'discount_amount' => 0,
        'line_total' => 100,
        'product_name' => $product->name,
        'product_sku' => $product->sku,
    ]);

    $response = $this->actingAs($user)->get(route('products.show', $product->slug));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/products/show')
        ->where('isAuthenticated', true)
        ->where('canReviewProduct', true)
    );
});

it('provides delivery estimate for in-stock product', function () {
    $product = createShowProduct(3104, 10);

    $response = $this->get(route('products.show', $product->slug));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/products/show')
        ->where('deliveryEstimate', 'Доставка 1-2 дня')
    );
});

it('provides null delivery estimate for out-of-stock product', function () {
    $product = createShowProduct(3105, 0);

    $response = $this->get(route('products.show', $product->slug));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/products/show')
        ->where('deliveryEstimate', null)
    );
});

function createShowProduct(int $code, int $qty = 10): Product
{
    $category = Category::query()->create([
        'name' => 'Show Category '.Str::random(4),
        'slug' => 'show-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Show Brand '.Str::random(4),
        'slug' => 'show-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Show Product '.$code,
        'code' => $code,
        'slug' => 'show-product-'.$code,
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => $qty,
        'short_description' => 'Short',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 100,
        'status' => true,
        'is_approved' => true,
    ]);
}
