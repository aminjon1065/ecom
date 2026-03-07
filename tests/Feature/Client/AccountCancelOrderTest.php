<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

it('allows user to cancel own pending order and restores stock', function () {
    $user = User::factory()->create();
    $product = makeCancelableProduct(code: 6101, qty: 2);

    $order = Order::query()->create([
        'invoice_id' => 990001,
        'transaction_id' => 'TXN-CANCEL-1',
        'user_id' => $user->id,
        'amount' => 100,
        'subtotal' => 100,
        'discount_total' => 0,
        'shipping_total' => 0,
        'grand_total' => 100,
        'product_quantity' => 1,
        'payment_method' => 'cash',
        'payment_status' => false,
        'coupon' => null,
        'coupon_code' => null,
        'order_status' => 'pending',
    ]);

    OrderProduct::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'unit_price' => 100,
        'discount_amount' => 0,
        'line_total' => 300,
        'product_name' => $product->name,
        'product_sku' => $product->sku,
    ]);

    $response = $this->actingAs($user)->patch(route('account.orders.cancel', $order));

    $response->assertRedirect();

    expect($order->fresh()->order_status)->toBe('cancelled')
        ->and($product->fresh()->qty)->toBe(5);
});

it('forbids cancelling another user order', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $order = Order::query()->create([
        'invoice_id' => 990002,
        'transaction_id' => 'TXN-CANCEL-2',
        'user_id' => $owner->id,
        'amount' => 100,
        'subtotal' => 100,
        'discount_total' => 0,
        'shipping_total' => 0,
        'grand_total' => 100,
        'product_quantity' => 1,
        'payment_method' => 'cash',
        'payment_status' => false,
        'coupon' => null,
        'coupon_code' => null,
        'order_status' => 'pending',
    ]);

    $response = $this->actingAs($otherUser)->patch(route('account.orders.cancel', $order));

    $response->assertForbidden();
});

it('does not cancel delivered order', function () {
    $user = User::factory()->create();
    $product = makeCancelableProduct(code: 6103, qty: 7);

    $order = Order::query()->create([
        'invoice_id' => 990003,
        'transaction_id' => 'TXN-CANCEL-3',
        'user_id' => $user->id,
        'amount' => 100,
        'subtotal' => 100,
        'discount_total' => 0,
        'shipping_total' => 0,
        'grand_total' => 100,
        'product_quantity' => 1,
        'payment_method' => 'cash',
        'payment_status' => false,
        'coupon' => null,
        'coupon_code' => null,
        'order_status' => 'delivered',
    ]);

    OrderProduct::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 100,
        'discount_amount' => 0,
        'line_total' => 200,
        'product_name' => $product->name,
        'product_sku' => $product->sku,
    ]);

    $response = $this->actingAs($user)->patch(route('account.orders.cancel', $order));

    $response->assertRedirect();

    expect($order->fresh()->order_status)->toBe('delivered')
        ->and($product->fresh()->qty)->toBe(7);
});

function makeCancelableProduct(int $code, int $qty): Product
{
    $category = Category::query()->create([
        'name' => 'Cancel Category '.Str::random(4),
        'slug' => 'cancel-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Cancel Brand '.Str::random(4),
        'slug' => 'cancel-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Cancel Product '.$code,
        'code' => $code,
        'slug' => 'cancel-product-'.$code,
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
