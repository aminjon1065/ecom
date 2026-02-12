<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        ]);
    }

    public function destroy(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return redirect()->back();
    }
}
