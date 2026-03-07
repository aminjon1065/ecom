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

function makeFlowProduct(int $code, float $price, int $qty): Product
{
    $category = Category::query()->create([
        'name' => 'Flow Category '.Str::random(4),
        'slug' => 'flow-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Flow Brand '.Str::random(4),
        'slug' => 'flow-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Flow Product '.$code,
        'code' => $code,
        'slug' => 'flow-product-'.$code,
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

function makeShippingRule(): ShippingRules
{
    return ShippingRules::query()->create([
        'name' => 'Flow Shipping',
        'type' => 'flat',
        'min_cost' => null,
        'cost' => 10,
        'status' => true,
    ]);
}

it('does not create duplicate orders for same idempotency key', function () {
    $user = User::factory()->create();
    $address = UserAddress::query()->create([
        'user_id' => $user->id,
        'address' => 'Street 10',
        'description' => null,
    ]);
    $shipping = makeShippingRule();
    $product = makeFlowProduct(code: 9101, price: 100, qty: 5);

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $payload = [
        'address_id' => $address->id,
        'payment_method' => 'cash',
        'shipping_rule_id' => $shipping->id,
        'coupon_code' => null,
        'idempotency_key' => 'same-key-1',
    ];

    $firstResponse = $this->actingAs($user)->post(route('checkout.store'), $payload);
    $firstOrder = Order::query()->latest('id')->firstOrFail();

    $firstResponse->assertRedirect(route('account.orders.show', $firstOrder));

    $secondResponse = $this->actingAs($user)->post(route('checkout.store'), $payload);
    $secondResponse->assertRedirect(route('account.orders.show', $firstOrder));

    expect(Order::query()->count())->toBe(1)
        ->and($product->fresh()->qty)->toBe(3);
});

it('returns error and does not create order when stock is insufficient', function () {
    $user = User::factory()->create();
    $address = UserAddress::query()->create([
        'user_id' => $user->id,
        'address' => 'Street 11',
        'description' => null,
    ]);
    $shipping = makeShippingRule();
    $product = makeFlowProduct(code: 9102, price: 120, qty: 1);

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response = $this->actingAs($user)->post(route('checkout.store'), [
        'address_id' => $address->id,
        'payment_method' => 'cash',
        'shipping_rule_id' => $shipping->id,
        'idempotency_key' => 'low-stock-key-1',
    ]);

    $response->assertSessionHasErrors('checkout');

    expect(Order::query()->count())->toBe(0)
        ->and($product->fresh()->qty)->toBe(1)
        ->and(Cart::query()->where('user_id', $user->id)->exists())->toBeTrue();
});
