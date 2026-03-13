<?php

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductViewEvent;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

it('calculates funnel KPI and top products on admin dashboard', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $viewerA = User::factory()->create();
    $viewerB = User::factory()->create();

    $topProduct = createMetricProduct(9301, 100);
    $secondProduct = createMetricProduct(9302, 80);

    ProductViewEvent::query()->create([
        'product_id' => $topProduct->id,
        'user_id' => $viewerA->id,
        'session_id' => 'sess-a',
        'viewed_at' => now(),
    ]);

    ProductViewEvent::query()->create([
        'product_id' => $secondProduct->id,
        'user_id' => $viewerB->id,
        'session_id' => 'sess-b',
        'viewed_at' => now(),
    ]);

    Cart::query()->create([
        'user_id' => $viewerA->id,
        'product_id' => $topProduct->id,
        'quantity' => 1,
    ]);

    $order = Order::query()->create([
        'invoice_id' => 550001,
        'transaction_id' => 'TXN-METRICS-1',
        'user_id' => $viewerA->id,
        'grand_total' => 300,
        'subtotal' => 300,
        'discount_total' => 0,
        'shipping_total' => 0,
        'grand_total' => 300,
        'product_quantity' => 3,
        'payment_method' => 'cash',
        'payment_status' => true,
        'coupon_code' => null,
        'coupon_code' => null,
        'order_status' => 'delivered',
    ]);

    OrderProduct::query()->create([
        'order_id' => $order->id,
        'product_id' => $topProduct->id,
        'quantity' => 2,
        'unit_price' => 100,
        'discount_amount' => 0,
        'line_total' => 200,
        'product_name' => $topProduct->name,
        'product_sku' => $topProduct->sku,
    ]);

    OrderProduct::query()->create([
        'order_id' => $order->id,
        'product_id' => $secondProduct->id,
        'quantity' => 1,
        'unit_price' => 80,
        'discount_amount' => 0,
        'line_total' => 80,
        'product_name' => $secondProduct->name,
        'product_sku' => $secondProduct->sku,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/dashboard')
        ->where('funnelMetrics.viewers', 2)
        ->where('funnelMetrics.cart_users', 1)
        ->where('funnelMetrics.buyers', 1)
        ->where('funnelMetrics.view_to_cart', 50)
        ->where('funnelMetrics.cart_to_order', 100)
        ->where('funnelMetrics.view_to_order', 50)
        ->where('topProducts.0.id', $topProduct->id)
        ->where('topProducts.0.sold_qty', 2)
        ->where('topProducts.0.gross_revenue', 200)
    );
});

it('filters dashboard KPI metrics by selected period', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $recentUser = User::factory()->create();
    $oldUser = User::factory()->create();

    $recentProduct = createMetricProduct(9401, 100);
    $oldProduct = createMetricProduct(9402, 100);

    ProductViewEvent::query()->create([
        'product_id' => $recentProduct->id,
        'user_id' => $recentUser->id,
        'session_id' => 'recent-session',
        'viewed_at' => now()->subDays(2),
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2),
    ]);

    ProductViewEvent::query()->create([
        'product_id' => $oldProduct->id,
        'user_id' => $oldUser->id,
        'session_id' => 'old-session',
        'viewed_at' => now()->subDays(40),
        'created_at' => now()->subDays(40),
        'updated_at' => now()->subDays(40),
    ]);

    $recentCart = Cart::query()->create([
        'user_id' => $recentUser->id,
        'product_id' => $recentProduct->id,
        'quantity' => 1,
    ]);
    $recentCart->forceFill([
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2),
    ])->save();

    $oldCart = Cart::query()->create([
        'user_id' => $oldUser->id,
        'product_id' => $oldProduct->id,
        'quantity' => 1,
    ]);
    $oldCart->forceFill([
        'created_at' => now()->subDays(40),
        'updated_at' => now()->subDays(40),
    ])->save();

    $recentOrder = Order::query()->create([
        'invoice_id' => 560001,
        'transaction_id' => 'TXN-METRICS-RECENT',
        'user_id' => $recentUser->id,
        'grand_total' => 100,
        'subtotal' => 100,
        'discount_total' => 0,
        'shipping_total' => 0,
        'grand_total' => 100,
        'product_quantity' => 1,
        'payment_method' => 'cash',
        'payment_status' => true,
        'coupon_code' => null,
        'coupon_code' => null,
        'order_status' => 'delivered',
    ]);
    $recentOrder->forceFill([
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2),
    ])->save();

    $oldOrder = Order::query()->create([
        'invoice_id' => 560002,
        'transaction_id' => 'TXN-METRICS-OLD',
        'user_id' => $oldUser->id,
        'grand_total' => 100,
        'subtotal' => 100,
        'discount_total' => 0,
        'shipping_total' => 0,
        'grand_total' => 100,
        'product_quantity' => 1,
        'payment_method' => 'cash',
        'payment_status' => true,
        'coupon_code' => null,
        'coupon_code' => null,
        'order_status' => 'delivered',
    ]);
    $oldOrder->forceFill([
        'created_at' => now()->subDays(40),
        'updated_at' => now()->subDays(40),
    ])->save();

    attachMetricOrderProduct($recentOrder, $recentProduct, 1);
    attachMetricOrderProduct($oldOrder, $oldProduct, 1);

    $response = $this->actingAs($admin)->get(route('admin.dashboard', [
        'period' => '7',
    ]));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/dashboard')
        ->where('metricsPeriod', '7')
        ->where('funnelMetrics.viewers', 1)
        ->where('funnelMetrics.cart_users', 1)
        ->where('funnelMetrics.buyers', 1)
        ->where('topProducts.0.id', $recentProduct->id)
    );
});

function createMetricProduct(int $code, float $price): Product
{
    $category = Category::query()->create([
        'name' => 'Metric Category '.Str::random(4),
        'slug' => 'metric-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Metric Brand '.Str::random(4),
        'slug' => 'metric-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Metric Product '.$code,
        'code' => $code,
        'slug' => 'metric-product-'.$code,
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

function attachMetricOrderProduct(Order $order, Product $product, int $quantity): void
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
