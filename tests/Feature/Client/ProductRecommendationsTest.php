<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

it('returns related products ordered by category brand and price relevance', function () {
    $categoryA = makeRecCategory('Rec A');
    $categoryB = makeRecCategory('Rec B');

    $brandA = makeRecBrand('Brand A');
    $brandB = makeRecBrand('Brand B');

    $mainProduct = makeRecProduct([
        'code' => 8101,
        'slug' => 'rec-main',
        'category_id' => $categoryA->id,
        'brand_id' => $brandA->id,
        'price' => 100,
    ]);

    $bestMatch = makeRecProduct([
        'code' => 8102,
        'slug' => 'rec-best',
        'category_id' => $categoryA->id,
        'brand_id' => $brandA->id,
        'price' => 103,
    ]);

    $categoryMatch = makeRecProduct([
        'code' => 8103,
        'slug' => 'rec-category',
        'category_id' => $categoryA->id,
        'brand_id' => $brandB->id,
        'price' => 99,
    ]);

    $brandMatch = makeRecProduct([
        'code' => 8104,
        'slug' => 'rec-brand',
        'category_id' => $categoryB->id,
        'brand_id' => $brandA->id,
        'price' => 100,
    ]);

    makeRecProduct([
        'code' => 8105,
        'slug' => 'rec-hidden',
        'category_id' => $categoryA->id,
        'brand_id' => $brandA->id,
        'price' => 102,
        'status' => false,
    ]);

    $response = $this->get(route('products.show', $mainProduct->slug));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/products/show')
        ->where('relatedProducts.0.id', $bestMatch->id)
        ->where('relatedProducts.1.id', $categoryMatch->id)
        ->where('relatedProducts.2.id', $brandMatch->id)
    );
});

it('returns also bought products by co-order frequency and excludes cancelled orders', function () {
    $category = makeRecCategory('Also Bought');
    $brand = makeRecBrand('Also Brand');

    $mainProduct = makeRecProduct([
        'code' => 8201,
        'slug' => 'also-main',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'price' => 100,
    ]);

    $firstCoBought = makeRecProduct([
        'code' => 8202,
        'slug' => 'also-first',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'price' => 120,
    ]);

    $secondCoBought = makeRecProduct([
        'code' => 8203,
        'slug' => 'also-second',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'price' => 130,
    ]);

    $cancelledOnlyProduct = makeRecProduct([
        'code' => 8204,
        'slug' => 'also-cancelled',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'price' => 140,
    ]);

    $buyer = User::factory()->create();

    $order1 = makeRecOrder($buyer, 'processing', 1);
    attachOrderProduct($order1, $mainProduct, 1);
    attachOrderProduct($order1, $firstCoBought, 1);

    $order2 = makeRecOrder($buyer, 'delivered', 2);
    attachOrderProduct($order2, $mainProduct, 1);
    attachOrderProduct($order2, $firstCoBought, 1);
    attachOrderProduct($order2, $secondCoBought, 1);

    $cancelledOrder = makeRecOrder($buyer, 'cancelled', 3);
    attachOrderProduct($cancelledOrder, $mainProduct, 1);
    attachOrderProduct($cancelledOrder, $cancelledOnlyProduct, 1);

    $response = $this->get(route('products.show', $mainProduct->slug));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/products/show')
        ->has('alsoBoughtProducts', 2)
        ->where('alsoBoughtProducts.0.id', $firstCoBought->id)
        ->where('alsoBoughtProducts.1.id', $secondCoBought->id)
    );
});

function makeRecCategory(string $name): Category
{
    return Category::query()->create([
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);
}

function makeRecBrand(string $name): Brand
{
    return Brand::query()->create([
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function makeRecProduct(array $attributes): Product
{
    return Product::query()->create(array_merge([
        'name' => 'Rec Product '.Str::random(4),
        'code' => random_int(9000, 9999),
        'slug' => 'rec-product-'.Str::lower(Str::random(8)),
        'thumb_image' => 'products/default.png',
        'category_id' => makeRecCategory('Default Category '.Str::random(3))->id,
        'brand_id' => makeRecBrand('Default Brand '.Str::random(3))->id,
        'qty' => 10,
        'short_description' => 'Short',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 100,
        'status' => true,
        'is_approved' => true,
    ], $attributes));
}

function makeRecOrder(User $user, string $status, int $seed): Order
{
    return Order::query()->create([
        'invoice_id' => 880000 + $seed,
        'transaction_id' => 'TXN-REC-'.$seed,
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
        'order_status' => $status,
    ]);
}

function attachOrderProduct(Order $order, Product $product, int $quantity): void
{
    OrderProduct::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => $quantity,
        'unit_price' => $product->price,
        'discount_amount' => 0,
        'line_total' => $product->price * $quantity,
        'product_name' => $product->name,
        'product_sku' => $product->sku,
    ]);
}
