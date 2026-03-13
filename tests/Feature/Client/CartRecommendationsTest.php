<?php

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ShippingRules;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

it('shows also bought recommendations on cart page', function () {
    $user = User::factory()->create();
    $buyer = User::factory()->create();

    $main = makeCartRecProduct(9901, 100);
    $first = makeCartRecProduct(9902, 80);
    $second = makeCartRecProduct(9903, 70);

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $main->id,
        'quantity' => 1,
    ]);

    $order1 = makeCartRecOrder($buyer, 'delivered', 1);
    attachCartRecOrderProduct($order1, $main, 1);
    attachCartRecOrderProduct($order1, $first, 1);

    $order2 = makeCartRecOrder($buyer, 'processing', 2);
    attachCartRecOrderProduct($order2, $main, 1);
    attachCartRecOrderProduct($order2, $first, 1);
    attachCartRecOrderProduct($order2, $second, 1);

    $cancelledOrder = makeCartRecOrder($buyer, 'cancelled', 3);
    attachCartRecOrderProduct($cancelledOrder, $main, 1);
    attachCartRecOrderProduct($cancelledOrder, makeCartRecProduct(9904, 60), 1);

    $response = $this->actingAs($user)->get(route('cart.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/cart')
        ->where('recommendedProducts.0.id', $first->id)
        ->where('recommendedProducts.1.id', $second->id)
    );
});

it('shares cart savings and free-shipping progress on cart page', function () {
    $user = User::factory()->create();

    $product = makeCartRecProduct(9910, 100);
    $product->update([
        'offer_price' => 80,
        'offer_start_date' => now()->subDay(),
        'offer_end_date' => now()->addDay(),
    ]);

    ShippingRules::query()->create([
        'name' => 'Free from 300',
        'type' => 'min_cost',
        'min_cost' => 300,
        'cost' => 25,
        'status' => true,
    ]);

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response = $this->actingAs($user)->get(route('cart.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/cart')
        ->where('cartSummary.subtotal', 160)
        ->where('cartSummary.savings', 40)
        ->where('cartSummary.free_shipping_threshold', 300)
        ->where('cartSummary.remaining_to_free_shipping', 140)
    );
});

function makeCartRecProduct(int $code, float $price): Product
{
    $category = Category::query()->create([
        'name' => 'Cart Rec Category '.Str::random(4),
        'slug' => 'cart-rec-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Cart Rec Brand '.Str::random(4),
        'slug' => 'cart-rec-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Cart Rec Product '.$code,
        'code' => $code,
        'slug' => 'cart-rec-product-'.$code,
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 10,
        'short_description' => 'Short',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => $price,
        'status' => true,
        'is_approved' => true,
    ]);
}

function makeCartRecOrder(User $user, string $status, int $seed): Order
{
    return Order::query()->create([
        'invoice_id' => 970000 + $seed,
        'transaction_id' => 'TXN-CART-REC-'.$seed,
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

function attachCartRecOrderProduct(Order $order, Product $product, int $quantity): void
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
