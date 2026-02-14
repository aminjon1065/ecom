<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsletterSubscriberController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email',
        ]);

        NewsletterSubscriber::create([
            'email' => $validated['email'],
            'verified_token' => Str::random(32),
            'is_verified' => false,
        ]);

        return redirect()->back();
    }
}
