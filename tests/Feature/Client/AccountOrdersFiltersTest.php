<?php

use App\Models\Order;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('filters account orders by number status and date range', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $matchingOrder = createAccountOrder($user, 900111, 'delivered', '2026-03-01 10:00:00');

    createAccountOrder($user, 900112, 'pending', '2026-03-01 12:00:00');
    createAccountOrder($user, 123000, 'delivered', '2026-02-15 12:00:00');
    createAccountOrder($otherUser, 900111, 'delivered', '2026-03-01 10:00:00');

    $response = $this->actingAs($user)->get(route('account.orders', [
        'search' => '9001',
        'status' => 'delivered',
        'date_from' => '2026-03-01',
        'date_to' => '2026-03-07',
    ]));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/account/orders')
        ->where('filters.search', '9001')
        ->where('filters.status', 'delivered')
        ->where('filters.date_from', '2026-03-01')
        ->where('filters.date_to', '2026-03-07')
        ->where('orders.total', 1)
        ->where('orders.data.0.id', $matchingOrder->id)
    );
});

it('validates account orders date range filters', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('account.orders', [
        'date_from' => '2026-03-10',
        'date_to' => '2026-03-01',
    ]));

    $response->assertSessionHasErrors('date_to');
});

function createAccountOrder(User $user, int $invoiceId, string $status, string $createdAt): Order
{
    $order = Order::query()->create([
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

    $order->forceFill([
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ])->save();

    return $order;
}
