<?php

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingRules;
use App\Models\User;
use App\Models\UserAddress;

// ─── helpers ─────────────────────────────────────────────────────────────────

function makeBrowserProduct(string $slug): Product
{
    $category = Category::query()->create([
        'name' => 'Browser Category',
        'slug' => 'browser-category',
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Browser Brand',
        'slug' => 'browser-brand',
        'logo' => 'brands/default.png',
        'status' => true,
    ]);

    return Product::query()->create([
        'name' => 'Тестовый товар',
        'code' => 99001,
        'slug' => $slug,
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 10,
        'short_description' => 'Short description',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 150.00,
        'status' => true,
        'is_approved' => true,
    ]);
}

// ─── tests ───────────────────────────────────────────────────────────────────

it('product show page renders correctly for an authenticated user', function () {
    $user = User::factory()->create();
    $product = makeBrowserProduct('browser-test-product');

    $this->actingAs($user);

    $page = visit('/products/'.$product->slug);

    $page->assertSee('Тестовый товар')
        ->assertSee('150')
        ->assertSee('Добавить в корзину')
        ->assertNoJavascriptErrors();
});

it('product show page renders correctly for a guest', function () {
    $product = makeBrowserProduct('browser-guest-product');

    $page = visit('/products/'.$product->slug);

    $page->assertSee('Тестовый товар')
        ->assertSee('150')
        ->assertNoJavascriptErrors();
});

it('checkout page shows cart items, address selector, and shipping options', function () {
    $user = User::factory()->create();
    $product = makeBrowserProduct('checkout-flow-product');

    ShippingRules::query()->create([
        'name' => 'Стандартная доставка',
        'type' => 'flat',
        'cost' => 50,
        'min_cost' => null,
        'status' => true,
    ]);

    UserAddress::query()->create([
        'user_id' => $user->id,
        'address' => 'ул. Тестовая, д. 1',
        'description' => 'Главный офис',
    ]);

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $this->actingAs($user);

    $page = visit('/checkout');

    $page->assertSee('Тестовый товар')
        ->assertSee('Адрес доставки')
        ->assertSee('ул. Тестовая, д. 1')
        ->assertSee('Способ доставки')
        ->assertSee('Стандартная доставка')
        ->assertSee('Оформить заказ')
        ->assertNoJavascriptErrors();
});

it('checkout redirects to cart when cart is empty', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $page = visit('/checkout');

    // CheckoutController redirects to /cart when empty; cart page shows the empty state
    $page->assertPathIs('/cart')
        ->assertSee('Корзина пуста')
        ->assertNoJavascriptErrors();
});

it('authenticated user can complete checkout and order is created', function () {
    $user = User::factory()->create();
    $product = makeBrowserProduct('place-order-product');

    $shipping = ShippingRules::query()->create([
        'name' => 'Доставка',
        'type' => 'flat',
        'cost' => 25,
        'min_cost' => null,
        'status' => true,
    ]);

    $address = UserAddress::query()->create([
        'user_id' => $user->id,
        'address' => 'ул. Заказная, д. 5',
        'description' => 'Офис',
    ]);

    Cart::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user);

    $page = visit('/checkout');

    // Select address and shipping rule, then place the order
    $page->radio('address', (string) $address->id)
        ->radio('shipping', (string) $shipping->id)
        ->click('Оформить заказ')
        ->assertNoJavascriptErrors();

    // Order should have been created and cart cleared
    expect(Order::query()->where('user_id', $user->id)->exists())->toBeTrue();
    expect(Cart::query()->where('user_id', $user->id)->exists())->toBeFalse();
});
