<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    Role::query()->firstOrCreate([
        'name' => 'user',
        'guard_name' => 'web',
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+992901112233',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home', absolute: false));
});
