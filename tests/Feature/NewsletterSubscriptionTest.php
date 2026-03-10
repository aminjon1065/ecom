<?php

use App\Mail\NewsletterConfirmationMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Mail;

it('stores newsletter subscription and sends confirmation email', function () {
    Mail::fake();

    $response = $this->post(route('newsletter.store'), [
        'email' => 'client@example.com',
    ]);

    $response->assertRedirect();

    $subscriber = NewsletterSubscriber::query()
        ->where('email', 'client@example.com')
        ->first();

    expect($subscriber)->not->toBeNull()
        ->and($subscriber?->is_verified)->toBeFalse()
        ->and($subscriber?->verified_token)->not->toBeEmpty();

    Mail::assertSent(NewsletterConfirmationMail::class, function (NewsletterConfirmationMail $mail) use ($subscriber): bool {
        return $mail->hasTo('client@example.com')
            && str_contains($mail->verificationUrl, (string) $subscriber?->verified_token);
    });
});

it('resends confirmation email for existing unverified subscription without creating duplicate', function () {
    Mail::fake();

    $subscriber = NewsletterSubscriber::query()->create([
        'email' => 'client@example.com',
        'verified_token' => 'existing-token',
        'is_verified' => false,
    ]);

    $response = $this->post(route('newsletter.store'), [
        'email' => 'CLIENT@example.com',
    ]);

    $response->assertRedirect();

    expect(NewsletterSubscriber::query()->where('email', 'client@example.com')->count())
        ->toBe(1);

    $subscriber->refresh();

    expect($subscriber->verified_token)->not->toBe('existing-token');

    Mail::assertSent(NewsletterConfirmationMail::class);
});

it('verifies newsletter subscription by token', function () {
    $subscriber = NewsletterSubscriber::query()->create([
        'email' => 'client@example.com',
        'verified_token' => 'verify-me',
        'is_verified' => false,
    ]);

    $response = $this->get(route('newsletter.verify', 'verify-me'));

    $response->assertRedirect('/');

    expect($subscriber->fresh()?->is_verified)->toBeTrue();
});

it('validates newsletter email before storing subscription', function () {
    $response = $this->from('/')->post(route('newsletter.store'), [
        'email' => 'not-an-email',
    ]);

    $response->assertRedirect('/');
    $response->assertSessionHasErrors('email');
});
