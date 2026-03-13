<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

test('google phone registration creates a user from session payload', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    Role::query()->firstOrCreate([
        'name' => 'user',
        'guard_name' => 'web',
    ]);

    session([
        'google_user' => [
            'id' => 'google-123',
            'name' => 'Google User',
            'email' => 'google-user@example.com',
            'avatar' => 'https://example.com/avatar.jpg',
        ],
    ]);

    $this->post(route('auth.google.phone.store'), [
        'phone' => '+992901234567',
    ])->assertRedirect('/');

    $user = User::query()->where('google_id', 'google-123')->firstOrFail();

    expect($user->phone)->toBe('+992901234567')
        ->and($user->email)->toBe('google-user@example.com');

    $this->assertAuthenticatedAs($user);
    expect(session()->has('google_user'))->toBeFalse();
});

test('google phone registration validates phone format', function () {
    session([
        'google_user' => [
            'id' => 'google-456',
            'name' => 'Google User',
            'email' => 'google-user-2@example.com',
            'avatar' => null,
        ],
    ]);

    $this->post(route('auth.google.phone.store'), [
        'phone' => '12345',
    ])->assertSessionHasErrors('phone');
});
