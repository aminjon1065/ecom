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

function makeRaceProduct(int $code, int $qty): Product
{
    $category = Category::query()->create([
        'name' => 'Race Cat '.Str::random(4),
        'slug' => 'race-cat-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Race Brand '.Str::random(4),
        'slug' => 'race-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Race Product '.$code,
        'code' => $code,
        'slug' => 'race-product-'.$code,
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => $qty,
        'short_description' => 'Short',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 100.0,
        'status' => true,
        'is_approved' => true,
    ]);
}

function makeRaceSetup(int $productCode): array
{
    $product = makeRaceProduct($productCode, 1);

    $userA = User::factory()->create();
    $userB = User::factory()->create();

    Cart::query()->create(['user_id' => $userA->id, 'product_id' => $product->id, 'quantity' => 1]);
    Cart::query()->create(['user_id' => $userB->id, 'product_id' => $product->id, 'quantity' => 1]);

    $addressA = UserAddress::query()->create(['user_id' => $userA->id, 'address' => 'Race St A', 'description' => null]);
    $addressB = UserAddress::query()->create(['user_id' => $userB->id, 'address' => 'Race St B', 'description' => null]);

    $shipping = ShippingRules::query()->create([
        'name' => 'Race Free '.Str::random(3),
        'type' => 'free_shipping',
        'min_cost' => null,
        'cost' => 0,
        'status' => true,
    ]);

    return [$product, $userA, $userB, $addressA, $addressB, $shipping];
}

it('only one order succeeds when two users buy the last unit', function () {
    [$product, $userA, $userB, $addressA, $addressB, $shipping] = makeRaceSetup(6001);

    $responseA = $this->actingAs($userA)->post(route('checkout.store'), [
        'address_id' => $addressA->id,
        'payment_method' => 'cash',
        'shipping_rule_id' => $shipping->id,
        'idempotency_key' => 'race-user-a-6001',
    ]);

    $responseB = $this->actingAs($userB)->post(route('checkout.store'), [
        'address_id' => $addressB->id,
        'payment_method' => 'cash',
        'shipping_rule_id' => $shipping->id,
        'idempotency_key' => 'race-user-b-6001',
    ]);

    $orderCount = Order::query()->count();
    $finalQty = $product->fresh()->qty;

    // Exactly one order created, stock never goes negative
    expect($orderCount)->toBe(1)
        ->and($finalQty)->toBe(0);
});

it('stock quantity never goes negative after oversell attempt', function () {
    [$product, $userA, $userB, $addressA, $addressB, $shipping] = makeRaceSetup(6002);

    // First request — should succeed
    $this->actingAs($userA)->post(route('checkout.store'), [
        'address_id' => $addressA->id,
        'payment_method' => 'cash',
        'shipping_rule_id' => $shipping->id,
        'idempotency_key' => 'race-neg-a-6002',
    ]);

    // Second request — stock is already 0, should fail
    $responseB = $this->actingAs($userB)->post(route('checkout.store'), [
        'address_id' => $addressB->id,
        'payment_method' => 'cash',
        'shipping_rule_id' => $shipping->id,
        'idempotency_key' => 'race-neg-b-6002',
    ]);

    $responseB->assertSessionHasErrors('checkout');

    expect($product->fresh()->qty)->toBeGreaterThanOrEqual(0);
});

it('checkout fails with a helpful error when stock is zero', function () {
    $product = makeRaceProduct(6003, 0);
    $user = User::factory()->create();

    Cart::query()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    $address = UserAddress::query()->create(['user_id' => $user->id, 'address' => 'Zero St', 'description' => null]);
    $shipping = ShippingRules::query()->create([
        'name' => 'Zero Free',
        'type' => 'free_shipping',
        'min_cost' => null,
        'cost' => 0,
        'status' => true,
    ]);

    $this->actingAs($user)->post(route('checkout.store'), [
        'address_id' => $address->id,
        'payment_method' => 'cash',
        'shipping_rule_id' => $shipping->id,
        'idempotency_key' => 'race-zero-6003',
    ])->assertSessionHasErrors('checkout');

    expect(Order::query()->count())->toBe(0);
});
