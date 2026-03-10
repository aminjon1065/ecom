<?php

use App\Jobs\SendNewsletterBroadcastJob;
use App\Mail\NewsletterBroadcastMail;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function makeAdminForNewsletter(): User
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}

it('dispatches newsletter broadcast job from admin panel', function () {
    Queue::fake();

    $admin = makeAdminForNewsletter();

    $response = $this->actingAs($admin)->post(route('admin.subscriber.send'), [
        'subject' => 'Весенняя распродажа',
        'body' => 'Только для подписчиков: новые скидки в каталоге.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    Queue::assertPushed(SendNewsletterBroadcastJob::class, function (SendNewsletterBroadcastJob $job): bool {
        return $job->subject === 'Весенняя распродажа'
            && $job->body === 'Только для подписчиков: новые скидки в каталоге.';
    });
});

it('sends newsletter only to verified subscribers', function () {
    Mail::fake();

    NewsletterSubscriber::query()->create([
        'email' => 'verified@example.com',
        'verified_token' => 'verified-token',
        'is_verified' => true,
    ]);

    NewsletterSubscriber::query()->create([
        'email' => 'pending@example.com',
        'verified_token' => 'pending-token',
        'is_verified' => false,
    ]);

    $job = new SendNewsletterBroadcastJob(
        'Тестовая тема',
        'Текст рассылки',
    );

    $job->handle();

    Mail::assertSent(NewsletterBroadcastMail::class, function (NewsletterBroadcastMail $mail): bool {
        return $mail->hasTo('verified@example.com')
            && $mail->subjectLine === 'Тестовая тема';
    });

    Mail::assertNotSent(NewsletterBroadcastMail::class, function (NewsletterBroadcastMail $mail): bool {
        return $mail->hasTo('pending@example.com');
    });
});
