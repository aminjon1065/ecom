<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PhoneOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PhoneAuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('auth/phone-login');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{9,15}$/'],
        ], [
            'phone.required' => 'Введите номер телефона',
            'phone.regex' => 'Неверный формат номера телефона',
        ]);

        $phone = preg_replace('/[^0-9+]/', '', $validated['phone']);

        // Delete old OTPs for this phone
        PhoneOtp::where('phone', $phone)->delete();

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PhoneOtp::create([
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
        ]);

        // TODO: Send via SMS provider (Twilio, SMS.tj, etc.)
        // For now, log the OTP for testing
        Log::info("Phone OTP for {$phone}: {$otp}");

        return redirect()->back()->with('otpSent', true)->with('phone', $phone);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:6'],
        ], [
            'otp.required' => 'Введите код подтверждения',
            'otp.size' => 'Код должен содержать 6 цифр',
        ]);

        $phone = preg_replace('/[^0-9+]/', '', $validated['phone']);

        $phoneOtp = PhoneOtp::where('phone', $phone)
            ->where('otp', $validated['otp'])
            ->first();

        if (!$phoneOtp) {
            return redirect()->back()
                ->withErrors(['otp' => 'Неверный код подтверждения'])
                ->with('otpSent', true)
                ->with('phone', $phone);
        }

        if ($phoneOtp->isExpired()) {
            $phoneOtp->delete();
            return redirect()->back()
                ->withErrors(['otp' => 'Код подтверждения истёк. Запросите новый'])
                ->with('phone', $phone);
        }

        // Find or create user by phone
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            $user = User::create([
                'name' => 'Пользователь',
                'email' => $phone . '@phone.local',
                'phone' => $phone,
                'phone_verified_at' => now(),
                'password' => bcrypt(Str::random(32)),
                'is_active' => 1,
            ]);
            $user->assignRole('user');
        } else {
            if (!$user->phone_verified_at) {
                $user->update(['phone_verified_at' => now()]);
            }
        }

        // Clean up OTP
        PhoneOtp::where('phone', $phone)->delete();

        // Login
        Auth::login($user, true);

        return redirect()->intended('/');
    }
}
