<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceDroppedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Product $product,
        public readonly float $currentPrice,
        public readonly float $targetPrice,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Цена на отслеживаемый товар снижена')
            ->greeting("Здравствуйте, {$notifiable->name}!")
            ->line("Цена на товар {$this->product->name} снизилась.")
            ->line("Новая цена: {$this->currentPrice} сом.")
            ->line("Цена при подписке: {$this->targetPrice} сом.")
            ->action('Открыть товар', route('products.show', $this->product->slug));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'current_price' => $this->currentPrice,
            'target_price' => $this->targetPrice,
        ];
    }
}
