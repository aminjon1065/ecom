<?php

use App\Models\Order;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('filters recent orders on account dashboard by invoice number and status', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $matching = createDashboardOrder($user, 771001, 'delivered');
    createDashboardOrder($user, 771002, 'pending');
    createDashboardOrder($user, 888001, 'delivered');
    createDashboardOrder($otherUser, 771001, 'delivered');

    $response = $this->actingAs($user)->get(route('account.dashboard', [
        'search' => '7710',
        'status' => 'delivered',
    ]));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/account/dashboard')
        ->where('dashboardFilters.search', '7710')
        ->where('dashboardFilters.status', 'delivered')
        ->has('recentOrders', 1)
        ->where('recentOrders.0.id', $matching->id)
    );
});

it('validates account dashboard order status filter', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('account.dashboard', [
        'status' => 'unknown-status',
    ]));

    $response->assertSessionHasErrors('status');
});

it('shows only five latest orders on account dashboard', function () {
    $user = User::factory()->create();

    foreach (range(1, 7) as $index) {
        $order = createDashboardOrder($user, 880000 + $index, $index % 2 === 0 ? 'pending' : 'delivered');
        $order->forceFill([
            'created_at' => now()->subMinutes(8 - $index),
            'updated_at' => now()->subMinutes(8 - $index),
        ])->save();
    }

    $response = $this->actingAs($user)->get(route('account.dashboard'));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/account/dashboard')
        ->has('recentOrders', 5)
        ->where('recentOrders.0.invoice_id', 880007)
        ->where('recentOrders.4.invoice_id', 880003)
    );
});

function createDashboardOrder(User $user, int $invoiceId, string $status): Order
{
    return Order::query()->create([
        'invoice_id' => $invoiceId,
        'transaction_id' => 'TXN-'.$invoiceId,
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
