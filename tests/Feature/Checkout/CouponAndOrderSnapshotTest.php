<?php

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupons;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingRules;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Str;

function createCheckoutProduct(int $code, float $price, int $qty = 10): Product
{
    $category = Category::query()->create([
        'name' => 'Category '.Str::random(4),
        'slug' => 'category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Brand '.Str::random(4),
        'slug' => 'brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Product '.$code,
        'code' => $code,
        'slug' => 'product-'.$code,
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

function createCoupon(string $code, string $discountType = 'percent', float $discount = 10): Coupons
{
    return Coupons::query()->create([
        'name' => 'Coupon '.$code,
        'code' => $code,
        'quantity' => 10,
        'max_use' => 100,
        'usage_limit' => 100,
        'usage_per_user' => 1,
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addDay()->toDateString(),
        'discount_type' => $discountType,
        'discount' => $discount,
        'status' => true,
        'is_active' => true,
        'total_used' => 0,
    ]);
}

it('applies valid coupon on checkout', function () {
    $user = User::factory()->create();
    $product = createCheckoutProduct(code: 9001, price: 100, qty: 20);

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    createCoupon(code: 'TENOFF', discountType: 'percent', discount: 10);

    $response = $this->actingAs($user)->post(route('checkout.coupon.apply'), [
        'code' => 'TENOFF',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('appliedCoupon.code', 'TENOFF');
    $response->assertSessionHas('appliedCoupon.discount_type', 'percent');
});

it('creates order snapshot and consumes coupon', function () {
    $user = User::factory()->create();
    $product = createCheckoutProduct(code: 9002, price: 200, qty: 5);

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $address = UserAddress::query()->create([
        'user_id' => $user->id,
        'address' => 'Test street 1',
        'description' => 'home',
    ]);

    $shipping = ShippingRules::query()->create([
        'name' => 'Flat',
        'type' => 'flat',
        'min_cost' => null,
        'cost' => 50,
        'status' => true,
    ]);

    $coupon = createCoupon(code: 'FIXED20', discountType: 'fixed', discount: 20);

    $response = $this->actingAs($user)->post(route('checkout.store'), [
        'address_id' => $address->id,
        'payment_method' => 'cash',
        'shipping_rule_id' => $shipping->id,
        'coupon_code' => 'FIXED20',
        'idempotency_key' => 'checkout-snapshot-1',
    ]);

    $order = Order::query()->latest('id')->firstOrFail();

    $response->assertRedirect(route('account.orders.show', $order));

    expect($order->subtotal)->toBe(400.0)
        ->and($order->shipping_total)->toBe(50.0)
        ->and($order->discount_total)->toBe(20.0)
        ->and($order->grand_total)->toBe(430.0)
        ->and($order->amount)->toBe(430.0)
        ->and($order->coupon_code)->toBe('FIXED20');

    $orderItem = $order->products()->firstOrFail();
    expect($orderItem->unit_price)->toBe(200.0)
        ->and($orderItem->discount_amount)->toBe(20.0)
        ->and($orderItem->line_total)->toBe(380.0)
        ->and($orderItem->product_name)->toBe($product->name)
        ->and($orderItem->product_sku)->toBeNull();

    expect($coupon->fresh()->total_used)->toBe(1)
        ->and($coupon->fresh()->quantity)->toBe(9)
        ->and($product->fresh()->qty)->toBe(3)
        ->and(Cart::query()->where('user_id', $user->id)->exists())->toBeFalse();
});
