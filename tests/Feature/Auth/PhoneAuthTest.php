<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('phone login screen can be rendered', function () {
    $response = $this->get(route('auth.phone.login'));
    $response->assertStatus(200);
});

test('can send verification code to valid phone', function () {
    $response = $this->post(route('auth.phone.store'), [
        'phone' => '+992927777777',
    ]);

    $response->assertRedirect(route('auth.phone.verify', ['phone' => '+992927777777']));

    $user = User::where('phone', '+992927777777')->first();
    expect($user)->not->toBeNull();
    expect($user->verification_code)->toBe('123456'); // Mock code
});

test('cannot verify with invalid code', function () {
    $user = User::factory()->create([
        'phone' => '+992928888888',
        'verification_code' => '123456',
    ]);

    $response = $this->post(route('auth.phone.confirm'), [
        'phone' => '+992928888888',
        'code' => '000000',
    ]);

    $response->assertSessionHasErrors(['code']);
    $this->assertGuest();
});

test('can verify and login with valid code', function () {
    $user = User::factory()->create([
        'phone' => '+992929999999',
        'verification_code' => '123456',
    ]);

    $response = $this->post(route('auth.phone.confirm'), [
        'phone' => '+992929999999',
        'code' => '123456',
    ]);

    $response->assertRedirect('/');
    $this->assertAuthenticatedAs($user);
    
    $user->refresh();
    expect($user->verification_code)->toBeNull();
    expect($user->phone_verified_at)->not->toBeNull();
});

test('validation fails for invalid phone format', function () {
    $response = $this->post(route('auth.phone.store'), [
        'phone' => '12345',
    ]);

    $response->assertSessionHasErrors(['phone']);
});
