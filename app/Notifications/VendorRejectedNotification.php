<?php

namespace App\Notifications;

use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Vendor $vendor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ваш магазин не был одобрен')
            ->greeting("Здравствуйте, {$notifiable->name}!")
            ->line("К сожалению, ваш магазин «{$this->vendor->shop_name}» не был одобрен.")
            ->line('Если у вас есть вопросы, пожалуйста, свяжитесь с нашей службой поддержки.')
            ->action('Связаться с поддержкой', route('home'))
            ->line('Спасибо за понимание.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'vendor_id' => $this->vendor->id,
            'shop_name' => $this->vendor->shop_name,
        ];
    }
}
