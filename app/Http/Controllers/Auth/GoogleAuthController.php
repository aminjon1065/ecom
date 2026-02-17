<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback from Google OAuth.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'google' => 'Не удалось войти через Google. Попробуйте снова.',
            ]);
        }

        $googleId = $googleUser->getId();

        // Find existing user by google_id
        $user = User::where('google_id', $googleId)->first();

        if ($user) {
            $user->update([
                'name' => $googleUser->getName(),
                'avatar' => $googleUser->getAvatar() ?: $user->avatar,
            ]);

            Auth::login($user, true);

            return redirect()->intended('/');
        }

        // Check if user exists with this email
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->update([
                'google_id' => $googleId,
                'avatar' => $googleUser->getAvatar() ?: $user->avatar,
            ]);

            Auth::login($user, true);

            return redirect()->intended('/');
        }

        // New user — store Google data in session, redirect to phone input
        session([
            'google_user' => [
                'id' => $googleId,
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
            ],
        ]);

        return redirect()->route('auth.google.phone');
    }

    /**
     * Show phone input form for new Google users.
     */
    public function showPhone(): Response|RedirectResponse
    {
        $googleUser = session('google_user');

        if (!$googleUser) {
            return redirect()->route('login');
        }

        return Inertia::render('auth/google-phone', [
            'googleUser' => $googleUser,
        ]);
    }

    /**
     * Complete registration with phone number.
     */
    public function storePhone(Request $request): RedirectResponse
    {
        $googleUser = session('google_user');

        if (!$googleUser) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{9,15}$/'],
        ], [
            'phone.required' => 'Введите номер телефона',
            'phone.regex' => 'Неверный формат номера телефона',
        ]);

        $phone = preg_replace('/[^0-9+]/', '', $validated['phone']);

        $user = User::create([
            'name' => $googleUser['name'],
            'email' => $googleUser['email'],
            'phone' => $phone,
            'google_id' => $googleUser['id'],
            'avatar' => $googleUser['avatar'],
            'email_verified_at' => now(),
            'password' => bcrypt(Str::random(32)),
            'is_active' => 1,
        ]);

        $user->assignRole('user');

        session()->forget('google_user');

        Auth::login($user, true);

        return redirect()->intended('/');
    }
}
