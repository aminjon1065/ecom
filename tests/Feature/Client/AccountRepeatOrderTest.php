<?php

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

it('adds available items from own order to cart when repeating order', function () {
    $user = User::factory()->create();

    $availableProduct = makeRepeatableProduct(code: 4101, qty: 4, status: true, approved: true);
    $unavailableProduct = makeRepeatableProduct(code: 4102, qty: 0, status: true, approved: true);
    $disabledProduct = makeRepeatableProduct(code: 4103, qty: 5, status: false, approved: true);

    $order = Order::query()->create([
        'invoice_id' => 730001,
        'transaction_id' => 'TXN-REPEAT-1',
        'user_id' => $user->id,
        'grand_total' => 200,
        'subtotal' => 200,
        'discount_total' => 0,
        'shipping_total' => 0,
        'grand_total' => 200,
        'product_quantity' => 3,
        'payment_method' => 'cash',
        'payment_status' => false,
        'coupon_code' => null,
        'coupon_code' => null,
        'order_status' => 'delivered',
    ]);

    OrderProduct::query()->create([
        'order_id' => $order->id,
        'product_id' => $availableProduct->id,
        'quantity' => 5,
        'unit_price' => 100,
        'discount_amount' => 0,
        'line_total' => 500,
        'product_name' => $availableProduct->name,
        'product_sku' => $availableProduct->sku,
    ]);

    OrderProduct::query()->create([
        'order_id' => $order->id,
        'product_id' => $unavailableProduct->id,
        'quantity' => 2,
        'unit_price' => 50,
        'discount_amount' => 0,
        'line_total' => 100,
        'product_name' => $unavailableProduct->name,
        'product_sku' => $unavailableProduct->sku,
    ]);

    OrderProduct::query()->create([
        'order_id' => $order->id,
        'product_id' => $disabledProduct->id,
        'quantity' => 1,
        'unit_price' => 50,
        'discount_amount' => 0,
        'line_total' => 50,
        'product_name' => $disabledProduct->name,
        'product_sku' => $disabledProduct->sku,
    ]);

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $availableProduct->id,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($user)->post(route('account.orders.repeat', $order));

    $response->assertRedirect(route('cart.index'));

    $cartItem = Cart::query()
        ->where('user_id', $user->id)
        ->where('product_id', $availableProduct->id)
        ->firstOrFail();

    expect($cartItem->quantity)->toBe(4)
        ->and(Cart::query()->where('user_id', $user->id)->count())->toBe(1);
});

it('forbids repeating another user order', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $product = makeRepeatableProduct(code: 4201, qty: 3, status: true, approved: true);

    $order = Order::query()->create([
        'invoice_id' => 730002,
        'transaction_id' => 'TXN-REPEAT-2',
        'user_id' => $owner->id,
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

    $response = $this->actingAs($otherUser)->post(route('account.orders.repeat', $order));

    $response->assertForbidden();
});

function makeRepeatableProduct(int $code, int $qty, bool $status, bool $approved): Product
{
    $category = Category::query()->create([
        'name' => 'Repeat Category '.Str::random(4),
        'slug' => 'repeat-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Repeat Brand '.Str::random(4),
        'slug' => 'repeat-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Repeat Product '.$code,
        'code' => $code,
        'slug' => 'repeat-product-'.$code,
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => $qty,
        'short_description' => 'Short',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 100,
        'status' => $status,
        'is_approved' => $approved,
    ]);
}
