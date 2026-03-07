<?php

namespace App\Jobs;

use App\Models\PriceAlert;
use App\Models\Product;
use App\Notifications\PriceDroppedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckPriceAlertsJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        PriceAlert::query()
            ->with(['user', 'product'])
            ->where('is_active', true)
            ->chunkById(200, function ($alerts): void {
                foreach ($alerts as $alert) {
                    $user = $alert->user;
                    /** @var Product|null $product */
                    $product = $alert->product;

                    if (! $user || ! $product || ! $product->status || ! $product->is_approved) {
                        continue;
                    }

                    $currentPrice = $this->effectivePrice($product);

                    if ($currentPrice >= $alert->target_price) {
                        continue;
                    }

                    if ($alert->last_notified_price !== null && $currentPrice >= $alert->last_notified_price) {
                        continue;
                    }

                    $user->notify(new PriceDroppedNotification(
                        product: $product,
                        currentPrice: $currentPrice,
                        targetPrice: $alert->target_price,
                    ));

                    $alert->update([
                        'last_notified_price' => $currentPrice,
                        'notified_at' => now(),
                    ]);
                }
            });
    }

    private function effectivePrice(Product $product): float
    {
        if (
            $product->offer_price &&
            $product->offer_start_date &&
            $product->offer_end_date &&
            now()->between($product->offer_start_date, $product->offer_end_date)
        ) {
            return (float) $product->offer_price;
        }

        return (float) $product->price;
    }
}
