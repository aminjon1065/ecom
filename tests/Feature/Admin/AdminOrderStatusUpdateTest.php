<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

it('admin can update order status through form request', function () {
    $admin = createOrderAdmin();
    $buyer = User::factory()->create();

    $order = Order::query()->create([
        'invoice_id' => 880001,
        'transaction_id' => 'TXN-ORDER-STATUS-1',
        'user_id' => $buyer->id,
        'grand_total' => 150,
        'subtotal' => 150,
        'discount_total' => 0,
        'shipping_total' => 0,
        'grand_total' => 150,
        'product_quantity' => 1,
        'payment_method' => 'cash',
        'payment_status' => false,
        'order_status' => OrderStatus::Pending,
    ]);

    $this->actingAs($admin)->patch(route('admin.order.update-status', $order), [
        'order_status' => OrderStatus::Processing->value,
    ])->assertRedirect();

    expect($order->fresh()->order_status)->toBe(OrderStatus::Processing);
});

it('admin order status update validates enum values', function () {
    $admin = createOrderAdmin();
    $buyer = User::factory()->create();

    $order = Order::query()->create([
        'invoice_id' => 880002,
        'transaction_id' => 'TXN-ORDER-STATUS-2',
        'user_id' => $buyer->id,
        'grand_total' => 150,
        'subtotal' => 150,
        'discount_total' => 0,
        'shipping_total' => 0,
        'grand_total' => 150,
        'product_quantity' => 1,
        'payment_method' => 'cash',
        'payment_status' => false,
        'order_status' => OrderStatus::Pending,
    ]);

    $this->actingAs($admin)->patch(route('admin.order.update-status', $order), [
        'order_status' => 'invalid-status',
    ])->assertSessionHasErrors('order_status');
});

function createOrderAdmin(): User
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}
