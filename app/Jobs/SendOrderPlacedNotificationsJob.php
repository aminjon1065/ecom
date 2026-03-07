<?php

namespace App\Jobs;

use App\Models\Order;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOrderPlacedNotificationsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly int $orderId,
    ) {}

    public function handle(): void
    {
        $order = Order::query()
            ->with('user')
            ->find($this->orderId);

        if (! $order || ! $order->user) {
            Log::warning('checkout.notification.order_not_found', [
                'order_id' => $this->orderId,
            ]);

            return;
        }

        $order->user->notify(new OrderPlacedNotification($order));
    }
}
