<?php

use App\Jobs\SendOrderPlacedNotificationsJob;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupons;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingRules;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

function makeSmokeProduct(int $code, float $price, int $qty): Product
{
    $category = Category::query()->create([
        'name' => 'Smoke Category '.Str::random(5),
        'slug' => 'smoke-category-'.Str::lower(Str::random(8)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Smoke Brand '.Str::random(5),
        'slug' => 'smoke-brand-'.Str::lower(Str::random(8)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Smoke Product '.$code,
        'code' => $code,
        'slug' => 'smoke-product-'.$code,
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

function makeSmokeCoupon(string $code): Coupons
{
    return Coupons::query()->create([
        'name' => 'Smoke coupon',
        'code' => $code,
        'quantity' => 20,
        'max_use' => 20,
        'usage_limit' => 20,
        'usage_per_user' => 1,
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'discount_type' => 'percent',
        'discount' => 10,
        'status' => true,
        'is_active' => true,
        'total_used' => 0,
    ]);
}

function makeSmokeShippingRule(): ShippingRules
{
    return ShippingRules::query()->create([
        'name' => 'Smoke Shipping',
        'type' => 'flat',
        'min_cost' => null,
        'cost' => 25,
        'status' => true,
    ]);
}

it('completes checkout flow and queues notification job', function () {
    Queue::fake();

    $user = User::factory()->create();
    $product = makeSmokeProduct(code: 9301, price: 120, qty: 5);
    $coupon = makeSmokeCoupon('SMOKE10');
    $shipping = makeSmokeShippingRule();
    $address = UserAddress::query()->create([
        'user_id' => $user->id,
        'address' => 'Smoke street 1',
        'description' => 'main',
    ]);

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $couponApplyResponse = $this->actingAs($user)->post(route('checkout.coupon.apply'), [
        'code' => $coupon->code,
    ]);

    $couponApplyResponse->assertRedirect();
    $couponApplyResponse->assertSessionHas('appliedCoupon.code', 'SMOKE10');

    $checkoutResponse = $this->actingAs($user)->post(route('checkout.store'), [
        'address_id' => $address->id,
        'payment_method' => 'cash',
        'shipping_rule_id' => $shipping->id,
        'coupon_code' => $coupon->code,
        'idempotency_key' => 'smoke-checkout-1',
    ]);

    $order = Order::query()->latest('id')->firstOrFail();

    $checkoutResponse->assertRedirect(route('account.orders.show', $order));

    expect($order->coupon_code)->toBe('SMOKE10')
        ->and($order->subtotal)->toBe(240.0)
        ->and($order->discount_total)->toBe(24.0)
        ->and($order->shipping_total)->toBe(25.0)
        ->and($order->grand_total)->toBe(241.0)
        ->and($product->fresh()->qty)->toBe(3)
        ->and(Cart::query()->where('user_id', $user->id)->exists())->toBeFalse();

    Queue::assertPushed(SendOrderPlacedNotificationsJob::class, 1);
});
