<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsletterSubscriberRequest;
use App\Mail\NewsletterConfirmationMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NewsletterSubscriberController extends Controller
{
    public function store(StoreNewsletterSubscriberRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $subscriber = NewsletterSubscriber::query()
            ->where('email', $validated['email'])
            ->first();

        if (! $subscriber) {
            $subscriber = NewsletterSubscriber::query()->create([
                'email' => $validated['email'],
                'verified_token' => Str::random(32),
                'is_verified' => false,
            ]);
        }

        $successMessage = 'Мы отправили письмо со ссылкой для подтверждения подписки.';

        if ($subscriber->is_verified) {
            return redirect()->back()
                ->with('success', 'Этот email уже подтверждён и подписан на рассылку.');
        }

        if (! $subscriber->is_verified) {
            $subscriber->forceFill([
                'verified_token' => Str::random(32),
            ])->save();

            Mail::to($subscriber->email)->send(
                new NewsletterConfirmationMail(
                    route('newsletter.verify', $subscriber->verified_token),
                ),
            );
        }

        return redirect()->back()->with('success', $successMessage);
    }

    public function verify(string $token): RedirectResponse
    {
        $subscriber = NewsletterSubscriber::query()
            ->where('verified_token', $token)
            ->firstOrFail();

        if (! $subscriber->is_verified) {
            $subscriber->forceFill([
                'is_verified' => true,
            ])->save();
        }

        return redirect('/')
            ->with('success', 'Подписка подтверждена. Теперь вы будете получать рассылку.');
    }
}
