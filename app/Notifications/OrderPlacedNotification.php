<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ваш заказ оформлен')
            ->greeting("Здравствуйте, {$notifiable->name}!")
            ->line("Заказ #{$this->order->invoice_id} успешно оформлен.")
            ->line("Сумма заказа: {$this->order->grand_total} сом.")
            ->action('Посмотреть заказ', route('account.orders.show', $this->order))
            ->line('Спасибо за покупку!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'invoice_id' => $this->order->invoice_id,
        ];
    }
}
