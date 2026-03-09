<?php

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingRules;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Str;

function createFlashSaleProduct(int $code, float $price, ?float $offerPrice = null, ?string $offerStart = null, ?string $offerEnd = null, int $qty = 10): Product
{
    $category = Category::query()->create([
        'name' => 'FS Category '.Str::random(4),
        'slug' => 'fs-cat-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'FS Brand '.Str::random(4),
        'slug' => 'fs-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'FS Product '.$code,
        'code' => $code,
        'slug' => 'fs-product-'.$code,
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => $qty,
        'short_description' => 'Short',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => $price,
        'offer_price' => $offerPrice,
        'offer_start_date' => $offerStart,
        'offer_end_date' => $offerEnd,
        'status' => true,
        'is_approved' => true,
    ]);
}

function flashSaleAddress(User $user): UserAddress
{
    return UserAddress::query()->create([
        'user_id' => $user->id,
        'address' => 'Flash Sale Street 1',
        'description' => 'home',
    ]);
}

it('uses offer price in cart subtotal when flash sale is active', function () {
    $user = User::factory()->create();
    $product = createFlashSaleProduct(
        code: 8001,
        price: 200.0,
        offerPrice: 150.0,
        offerStart: now()->subDay()->toDateString(),
        offerEnd: now()->addDay()->toDateString(),
    );

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $address = flashSaleAddress($user);
    $shipping = ShippingRules::query()->create([
        'name' => 'Free',
        'type' => 'free_shipping',
        'min_cost' => null,
        'cost' => 0,
        'status' => true,
    ]);

    $this->actingAs($user)->post(route('checkout.store'), [
        'address_id' => $address->id,
        'payment_method' => 'cash',
        'shipping_rule_id' => $shipping->id,
        'idempotency_key' => 'flash-sale-active-test',
    ]);

    $order = Order::query()->latest('id')->firstOrFail();

    // subtotal = 150 * 2 = 300 (offer price, not regular price of 200)
    expect($order->subtotal)->toBe(300.0)
        ->and($order->grand_total)->toBe(300.0);

    $item = $order->products()->firstOrFail();
    expect($item->unit_price)->toBe(150.0)
        ->and($item->line_total)->toBe(300.0);
});

it('uses regular price when flash sale has expired', function () {
    $user = User::factory()->create();
    $product = createFlashSaleProduct(
        code: 8002,
        price: 200.0,
        offerPrice: 150.0,
        offerStart: now()->subWeek()->toDateString(),
        offerEnd: now()->subDay()->toDateString(),
    );

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $address = flashSaleAddress($user);
    $shipping = ShippingRules::query()->create([
        'name' => 'Free2',
        'type' => 'free_shipping',
        'min_cost' => null,
        'cost' => 0,
        'status' => true,
    ]);

    $this->actingAs($user)->post(route('checkout.store'), [
        'address_id' => $address->id,
        'payment_method' => 'cash',
        'shipping_rule_id' => $shipping->id,
        'idempotency_key' => 'flash-sale-expired-test',
    ]);

    $order = Order::query()->latest('id')->firstOrFail();

    // subtotal = 200 (regular price, flash sale expired)
    expect($order->subtotal)->toBe(200.0);

    $item = $order->products()->firstOrFail();
    expect($item->unit_price)->toBe(200.0);
});

it('uses regular price when flash sale has not started yet', function () {
    $user = User::factory()->create();
    $product = createFlashSaleProduct(
        code: 8003,
        price: 200.0,
        offerPrice: 150.0,
        offerStart: now()->addDay()->toDateString(),
        offerEnd: now()->addWeek()->toDateString(),
    );

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $address = flashSaleAddress($user);
    $shipping = ShippingRules::query()->create([
        'name' => 'Free3',
        'type' => 'free_shipping',
        'min_cost' => null,
        'cost' => 0,
        'status' => true,
    ]);

    $this->actingAs($user)->post(route('checkout.store'), [
        'address_id' => $address->id,
        'payment_method' => 'cash',
        'shipping_rule_id' => $shipping->id,
        'idempotency_key' => 'flash-sale-future-test',
    ]);

    $order = Order::query()->latest('id')->firstOrFail();

    // subtotal = 200 (regular price, flash sale not started)
    expect($order->subtotal)->toBe(200.0);

    $item = $order->products()->firstOrFail();
    expect($item->unit_price)->toBe(200.0);
});

it('applies coupon discount on flash sale subtotal not regular price', function () {
    $user = User::factory()->create();
    $product = createFlashSaleProduct(
        code: 8004,
        price: 200.0,
        offerPrice: 100.0,
        offerStart: now()->subDay()->toDateString(),
        offerEnd: now()->addDay()->toDateString(),
        qty: 20,
    );

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    // coupon: 10% off — applied to flash sale subtotal (100*2=200), not regular (200*2=400)
    \App\Models\Coupons::query()->create([
        'name' => 'Flash 10% off',
        'code' => 'FLASH10',
        'quantity' => 5,
        'usage_limit' => 100,
        'usage_per_user' => 1,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
        'discount_type' => 'percent',
        'discount' => 10,
        'is_active' => true,
        'total_used' => 0,
    ]);

    $address = flashSaleAddress($user);
    $shipping = ShippingRules::query()->create([
        'name' => 'Free4',
        'type' => 'free_shipping',
        'min_cost' => null,
        'cost' => 0,
        'status' => true,
    ]);

    $this->actingAs($user)->post(route('checkout.store'), [
        'address_id' => $address->id,
        'payment_method' => 'cash',
        'shipping_rule_id' => $shipping->id,
        'coupon_code' => 'FLASH10',
        'idempotency_key' => 'flash-sale-coupon-test',
    ]);

    $order = Order::query()->latest('id')->firstOrFail();

    // subtotal = 100*2 = 200 (flash sale), discount = 20 (10% of 200), grand_total = 180
    expect($order->subtotal)->toBe(200.0)
        ->and($order->discount_total)->toBe(20.0)
        ->and($order->grand_total)->toBe(180.0);
});
