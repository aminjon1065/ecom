<?php

use App\Models\PhoneOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

test('phone login screen can be rendered', function () {
    $this->withoutVite();

    $response = $this->get(route('auth.phone'));

    $response->assertStatus(200);
});

test('can send otp to valid phone', function () {
    $phone = '+992927777777';

    $response = $this->post(route('auth.phone.otp'), [
        'phone' => $phone,
    ]);

    $response->assertRedirect();

    $otp = PhoneOtp::query()->where('phone', $phone)->first();
    expect($otp)->not->toBeNull();
    expect($otp?->otp)->toHaveLength(6);
});

test('cannot verify with invalid otp', function () {
    $phone = '+992928888888';

    PhoneOtp::create([
        'phone' => $phone,
        'otp' => '123456',
        'expires_at' => now()->addMinutes(5),
    ]);

    $response = $this->post(route('auth.phone.verify'), [
        'phone' => $phone,
        'otp' => '000000',
    ]);

    $response->assertSessionHasErrors(['otp']);
    $this->assertGuest();
});

test('can verify and login with valid otp', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    Role::query()->firstOrCreate([
        'name' => 'user',
        'guard_name' => 'web',
    ]);

    $phone = '+992929999999';

    PhoneOtp::create([
        'phone' => $phone,
        'otp' => '123456',
        'expires_at' => now()->addMinutes(5),
    ]);

    $response = $this->post(route('auth.phone.verify'), [
        'phone' => $phone,
        'otp' => '123456',
    ]);

    $response->assertRedirect('/');

    $user = User::query()->where('phone', $phone)->first();
    expect($user)->not->toBeNull();
    $this->assertAuthenticatedAs($user);
    expect($user?->phone_verified_at)->not->toBeNull();
    expect(PhoneOtp::query()->where('phone', $phone)->exists())->toBeFalse();
});

test('validation fails for invalid phone format', function () {
    $response = $this->post(route('auth.phone.otp'), [
        'phone' => '12345',
    ]);

    $response->assertSessionHasErrors(['phone']);
});

test('new phone user gets random password hash', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    Role::query()->firstOrCreate([
        'name' => 'user',
        'guard_name' => 'web',
    ]);

    $phone = '+992926666666';

    PhoneOtp::create([
        'phone' => $phone,
        'otp' => '654321',
        'expires_at' => now()->addMinutes(5),
    ]);

    $this->post(route('auth.phone.verify'), [
        'phone' => $phone,
        'otp' => '654321',
    ])->assertRedirect('/');

    $user = User::query()->where('phone', $phone)->firstOrFail();

    expect(Hash::check('password', $user->password))->toBeFalse();
});
