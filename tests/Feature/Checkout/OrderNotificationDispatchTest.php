<?php

use App\Jobs\SendOrderPlacedNotificationsJob;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ShippingRules;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

function makeNotificationProduct(int $code, float $price, int $qty): Product
{
    $category = Category::query()->create([
        'name' => 'Notification Category '.Str::random(4),
        'slug' => 'notification-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Notification Brand '.Str::random(4),
        'slug' => 'notification-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Notification Product '.$code,
        'code' => $code,
        'slug' => 'notification-product-'.$code,
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => $qty,
        'short_description' => 'Short',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => $price,
        'status' => true,
        'is_approved' => true,
    ]);
}

function makeNotificationShipping(): ShippingRules
{
    return ShippingRules::query()->create([
        'name' => 'Notification Shipping',
        'type' => 'flat',
        'min_cost' => null,
        'cost' => 15,
        'status' => true,
    ]);
}

it('dispatches order placed notification job after successful checkout', function () {
    Queue::fake();

    $user = User::factory()->create();
    $address = UserAddress::query()->create([
        'user_id' => $user->id,
        'address' => 'Notification street 1',
        'description' => null,
    ]);
    $shipping = makeNotificationShipping();
    $product = makeNotificationProduct(code: 9201, price: 90, qty: 5);

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response = $this->actingAs($user)->post(route('checkout.store'), [
        'address_id' => $address->id,
        'payment_method' => 'cash',
        'shipping_rule_id' => $shipping->id,
        'idempotency_key' => 'notification-success-1',
    ]);

    $response->assertRedirect();

    Queue::assertPushed(SendOrderPlacedNotificationsJob::class, 1);
});

it('does not dispatch order placed notification job when checkout fails', function () {
    Queue::fake();

    $user = User::factory()->create();
    $address = UserAddress::query()->create([
        'user_id' => $user->id,
        'address' => 'Notification street 2',
        'description' => null,
    ]);
    $shipping = makeNotificationShipping();
    $product = makeNotificationProduct(code: 9202, price: 90, qty: 1);

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    $response = $this->actingAs($user)->post(route('checkout.store'), [
        'address_id' => $address->id,
        'payment_method' => 'cash',
        'shipping_rule_id' => $shipping->id,
        'idempotency_key' => 'notification-fail-1',
    ]);

    $response->assertSessionHasErrors('checkout');

    Queue::assertNotPushed(SendOrderPlacedNotificationsJob::class);
});
