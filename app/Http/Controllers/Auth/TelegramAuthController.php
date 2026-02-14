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

class TelegramAuthController extends Controller
{
    /**
     * Show Telegram login page.
     */
    public function show(): Response
    {
        return Inertia::render('auth/telegram-login', [
            'telegramBotUsername' => config('services.telegram.bot_username'),
        ]);
    }

    /**
     * Handle Telegram Login Widget callback.
     * Validates the hash from Telegram and logs in / creates user.
     */
    public function callback(Request $request): RedirectResponse
    {
        $data = $request->only([
            'id', 'first_name', 'last_name', 'username',
            'photo_url', 'auth_date', 'hash',
        ]);

        if (!$this->validateTelegramData($data)) {
            return redirect()->route('login')->withErrors([
                'telegram' => 'Неверные данные авторизации Telegram. Попробуйте снова.',
            ]);
        }

        // Check auth_date is not too old (allow 1 day for safety)
        if ((time() - (int) $data['auth_date']) > 86400) {
            return redirect()->route('login')->withErrors([
                'telegram' => 'Данные авторизации устарели. Попробуйте снова.',
            ]);
        }

        $telegramId = (string) $data['id'];
        $firstName = $data['first_name'] ?? '';
        $lastName = $data['last_name'] ?? '';
        $fullName = trim("$firstName $lastName") ?: 'Пользователь';
        $username = $data['username'] ?? null;
        $photoUrl = $data['photo_url'] ?? null;

        // Find existing user by telegram_id or create new one
        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            // Check if user with telegram username email pattern exists (migration from old data)
            $user = User::create([
                'name' => $fullName,
                'email' => 'tg_' . $telegramId . '@telegram.local',
                'phone' => null,
                'telegram_id' => $telegramId,
                'telegram_username' => $username,
                'avatar' => $photoUrl,
                'email_verified_at' => now(),
                'password' => bcrypt(Str::random(32)),
                'is_active' => 1,
            ]);
            $user->assignRole('user');
        } else {
            // Update user info from Telegram (name, avatar, username)
            $user->update([
                'name' => $fullName,
                'telegram_username' => $username,
                'avatar' => $photoUrl ?: $user->avatar,
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended('/');
    }

    /**
     * Validate Telegram Login Widget data using bot token.
     *
     * @see https://core.telegram.org/widgets/login#checking-authorization
     */
    private function validateTelegramData(array $data): bool
    {
        if (empty($data['hash']) || empty($data['id']) || empty($data['auth_date'])) {
            return false;
        }

        $hash = $data['hash'];
        unset($data['hash']);

        // Sort data alphabetically by key
        ksort($data);

        // Build the data-check-string
        $dataCheckArr = [];
        foreach ($data as $key => $value) {
            if ($value !== null && $value !== '') {
                $dataCheckArr[] = "$key=$value";
            }
        }
        $dataCheckString = implode("\n", $dataCheckArr);

        // Create secret key: SHA256 hash of bot token
        $secretKey = hash('sha256', config('services.telegram.bot_token'), true);

        // Calculate HMAC-SHA256
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($calculatedHash, $hash);
    }
}
