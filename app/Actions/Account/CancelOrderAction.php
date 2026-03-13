<?php

namespace App\Actions\Account;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CancelOrderAction
{
    public function canCancel(Order $order): bool
    {
        return in_array($order->order_status, [OrderStatus::Pending, OrderStatus::Processing], true);
    }

    public function handle(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            /** @var \Illuminate\Database\Eloquent\Collection<int, OrderProduct> $orderProducts */
            $orderProducts = OrderProduct::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->get();

            foreach ($orderProducts as $orderProduct) {
                Product::query()
                    ->where('id', $orderProduct->product_id)
                    ->lockForUpdate()
                    ->increment('qty', $orderProduct->quantity);
            }

            $order->update([
                'order_status' => OrderStatus::Cancelled,
            ]);
        });
    }
}
