<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('allows user to change password with valid current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user)->put(route('account.password.update'), [
        'current_password' => 'old-password',
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $response->assertRedirect();

    expect(Hash::check('new-secure-password', $user->fresh()->password))->toBeTrue();
});

it('rejects password change with wrong current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user)->put(route('account.password.update'), [
        'current_password' => 'wrong-password',
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $response->assertSessionHasErrors('current_password');

    expect(Hash::check('old-password', $user->fresh()->password))->toBeTrue();
});

it('rejects password change when confirmation does not match', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user)->put(route('account.password.update'), [
        'current_password' => 'old-password',
        'password' => 'new-secure-password',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors('password');
});

it('rejects password shorter than 8 characters', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user)->put(route('account.password.update'), [
        'current_password' => 'old-password',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertSessionHasErrors('password');
});

it('allows social-only user to set password without current password', function () {
    $user = User::factory()->create([
        'telegram_id' => '123456789',
        'email' => 'tg_123456789@telegram.local',
        'password' => Hash::make('random-string'),
    ]);

    $response = $this->actingAs($user)->put(route('account.password.update'), [
        'password' => 'my-new-password',
        'password_confirmation' => 'my-new-password',
    ]);

    $response->assertRedirect();

    expect(Hash::check('my-new-password', $user->fresh()->password))->toBeTrue();
});

it('requires current password for non-social user', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user)->put(route('account.password.update'), [
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $response->assertSessionHasErrors('current_password');
});

it('redirects guests to login', function () {
    $response = $this->put(route('account.password.update'), [
        'current_password' => 'whatever',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect();
});
