<?php

namespace App\Jobs;

use App\Mail\NewsletterBroadcastMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendNewsletterBroadcastJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $subject,
        public string $body,
    ) {}

    public function handle(): void
    {
        NewsletterSubscriber::query()
            ->where('is_verified', true)
            ->orderBy('id')
            ->chunkById(100, function ($subscribers): void {
                foreach ($subscribers as $subscriber) {
                    Mail::to($subscriber->email)->send(
                        new NewsletterBroadcastMail($this->subject, $this->body),
                    );
                }
            });
    }
}
