<?php

namespace App\Notifications;

use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorApprovedNotification extends Notification
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
            ->subject('Ваш магазин одобрен')
            ->greeting("Здравствуйте, {$notifiable->name}!")
            ->line("Ваш магазин «{$this->vendor->shop_name}» был одобрен администратором.")
            ->line('Теперь вы можете добавлять товары и начать продавать.')
            ->action('Перейти в кабинет продавца', route('vendor.dashboard'))
            ->line('Спасибо, что выбрали нас!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'vendor_id' => $this->vendor->id,
            'shop_name' => $this->vendor->shop_name,
        ];
    }
}
