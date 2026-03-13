<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendPhoneOtpRequest;
use App\Http\Requests\Auth\VerifyPhoneOtpRequest;
use App\Models\PhoneOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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

    public function sendOtp(SendPhoneOtpRequest $request): RedirectResponse
    {
        $phone = preg_replace('/[^0-9+]/', '', $request->validated('phone'));

        PhoneOtp::where('phone', $phone)->delete();

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PhoneOtp::create([
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
        ]);

        Log::info("Phone OTP for {$phone}: {$otp}");

        return redirect()->back()->with('otpSent', true)->with('phone', $phone);
    }

    public function verifyOtp(VerifyPhoneOtpRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $phone = preg_replace('/[^0-9+]/', '', $validated['phone']);

        $phoneOtp = PhoneOtp::query()
            ->where('phone', $phone)
            ->where('otp', $validated['otp'])
            ->first();

        if (! $phoneOtp) {
            return redirect()->back()
                ->withErrors(['otp' => 'Неверный код подтверждения.'])
                ->with('otpSent', true)
                ->with('phone', $phone);
        }

        if ($phoneOtp->isExpired()) {
            $phoneOtp->delete();

            return redirect()->back()
                ->withErrors(['otp' => 'Код подтверждения истек. Запросите новый.'])
                ->with('phone', $phone);
        }

        $user = User::query()->where('phone', $phone)->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => 'Пользователь',
                'email' => $phone.'@phone.local',
                'phone' => $phone,
                'phone_verified_at' => now(),
                'password' => bcrypt(Str::random(32)),
                'is_active' => 1,
            ]);
            $user->assignRole('user');
        } elseif (! $user->phone_verified_at) {
            $user->update(['phone_verified_at' => now()]);
        }

        PhoneOtp::query()->where('phone', $phone)->delete();

        Auth::login($user, true);

        return redirect()->intended('/');
    }
}
