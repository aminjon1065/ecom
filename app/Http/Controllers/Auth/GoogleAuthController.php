<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreGooglePhoneRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $exception) {
            return redirect()->route('login')->withErrors([
                'google' => 'Не удалось войти через Google. Попробуйте снова.',
            ]);
        }

        $googleId = $googleUser->getId();

        $user = User::query()->where('google_id', $googleId)->first();

        if ($user) {
            $user->update([
                'name' => $googleUser->getName(),
                'avatar' => $googleUser->getAvatar() ?: $user->avatar,
            ]);

            Auth::login($user, true);

            return redirect()->intended('/');
        }

        $user = User::query()->where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->update([
                'google_id' => $googleId,
                'avatar' => $googleUser->getAvatar() ?: $user->avatar,
            ]);

            Auth::login($user, true);

            return redirect()->intended('/');
        }

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

    public function showPhone(): Response|RedirectResponse
    {
        $googleUser = session('google_user');

        if (! $googleUser) {
            return redirect()->route('login');
        }

        return Inertia::render('auth/google-phone', [
            'googleUser' => $googleUser,
        ]);
    }

    public function storePhone(StoreGooglePhoneRequest $request): RedirectResponse
    {
        /** @var array{id: string, name: string, email: string, avatar: ?string} $googleUser */
        $googleUser = session('google_user');

        if (! $googleUser) {
            return redirect()->route('login');
        }

        $phone = preg_replace('/[^0-9+]/', '', $request->validated('phone'));

        $user = User::query()->create([
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
