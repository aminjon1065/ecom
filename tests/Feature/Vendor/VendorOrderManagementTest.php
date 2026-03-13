<?php

use App\Enums\OrderStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function makeOrderVendorUser(): array
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $role = Role::query()->firstOrCreate(['name' => 'vendor', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    $vendor = Vendor::query()->create([
        'user_id' => $user->id,
        'shop_name' => 'Order Shop '.Str::random(4),
        'status' => true,
    ]);

    return [$user, $vendor];
}

function makeOrderWithVendorProduct(Vendor $vendor): array
{
    $category = Category::query()->create([
        'name' => 'OV Cat '.Str::random(4),
        'slug' => 'ov-cat-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'OV Brand '.Str::random(4),
        'slug' => 'ov-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    $product = Product::query()->create([
        'name' => 'OV Product '.Str::random(4),
        'code' => rand(50000, 59999),
        'slug' => 'ov-product-'.Str::lower(Str::random(8)),
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'vendor_id' => $vendor->id,
        'qty' => 10,
        'short_description' => 'Short',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 100.0,
        'status' => true,
        'is_approved' => true,
    ]);

    $buyer = User::factory()->create();

    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'invoice_id' => rand(100000, 999999),
        'order_status' => OrderStatus::Pending,
        'payment_method' => 'cash',
        'payment_status' => false,
        'grand_total' => 100.0,
        'product_quantity' => 1,
        'subtotal' => 100.0,
        'shipping_total' => 0.0,
        'discount_total' => 0.0,
        'grand_total' => 100.0,
        'idempotency_key' => 'ov-'.Str::random(16),
        'transaction_id' => 'TXN-'.strtoupper(Str::random(8)),
    ]);

    OrderProduct::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 100.0,
        'line_total' => 100.0,
    ]);

    return [$product, $order, $buyer];
}

it('vendor can update order status for their products', function () {
    [$vendorUser, $vendor] = makeOrderVendorUser();
    [, $order] = makeOrderWithVendorProduct($vendor);

    expect($order->order_status)->toBe(OrderStatus::Pending);

    $this->actingAs($vendorUser)
        ->patch(route('vendor.order.status', $order), ['order_status' => OrderStatus::Processing->value])
        ->assertRedirect();

    expect($order->fresh()->order_status)->toBe(OrderStatus::Processing);
});

it('vendor can advance order to shipped status', function () {
    [$vendorUser, $vendor] = makeOrderVendorUser();
    [, $order] = makeOrderWithVendorProduct($vendor);

    $this->actingAs($vendorUser)
        ->patch(route('vendor.order.status', $order), ['order_status' => OrderStatus::Shipped->value])
        ->assertRedirect();

    expect($order->fresh()->order_status)->toBe(OrderStatus::Shipped);
});

it('vendor cannot update order status for orders without their products', function () {
    [$vendorUser1, $vendor1] = makeOrderVendorUser();
    [$vendorUser2, $vendor2] = makeOrderVendorUser();

    [, $order] = makeOrderWithVendorProduct($vendor1);

    $this->actingAs($vendorUser2)
        ->patch(route('vendor.order.status', $order), ['order_status' => OrderStatus::Processing->value])
        ->assertForbidden();

    expect($order->fresh()->order_status)->toBe(OrderStatus::Pending);
});

it('vendor update status rejects invalid status values', function () {
    [$vendorUser, $vendor] = makeOrderVendorUser();
    [, $order] = makeOrderWithVendorProduct($vendor);

    $this->actingAs($vendorUser)
        ->patch(route('vendor.order.status', $order), ['order_status' => 'invalid_status'])
        ->assertSessionHasErrors('order_status');
});

it('vendor can view order list for their products', function () {
    [$vendorUser, $vendor] = makeOrderVendorUser();
    makeOrderWithVendorProduct($vendor);

    $this->actingAs($vendorUser)
        ->get(route('vendor.order.index'))
        ->assertOk();
});

it('vendor cannot access vendor dashboard without vendor role', function () {
    $plainUser = User::factory()->create();

    $this->actingAs($plainUser)
        ->get(route('vendor.order.index'))
        ->assertForbidden();
});
