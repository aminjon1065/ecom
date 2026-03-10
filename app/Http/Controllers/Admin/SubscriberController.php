<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendNewsletterBroadcastRequest;
use App\Jobs\SendNewsletterBroadcastJob;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SubscriberController extends Controller
{
    public function index(): Response
    {
        $subscribers = NewsletterSubscriber::latest()->paginate(15)->withQueryString();

        return Inertia::render('admin/subscriber/index', [
            'subscribers' => $subscribers,
            'stats' => [
                'total' => NewsletterSubscriber::query()->count(),
                'verified' => NewsletterSubscriber::query()->where('is_verified', true)->count(),
                'unverified' => NewsletterSubscriber::query()->where('is_verified', false)->count(),
            ],
        ]);
    }

    public function send(SendNewsletterBroadcastRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        SendNewsletterBroadcastJob::dispatch(
            $validated['subject'],
            $validated['body'],
        );

        return redirect()->back()->with('success', 'Рассылка поставлена в очередь на отправку.');
    }

    public function destroy(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return redirect()->back();
    }
}
