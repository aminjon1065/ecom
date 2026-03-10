<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $verificationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Подтвердите подписку на рассылку',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletter.confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
