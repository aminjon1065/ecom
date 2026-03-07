<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

test('password update page is displayed', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create();
    $user->assignRole($adminRole);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.user-password.edit'));

    $response->assertStatus(200);
});

test('password can be updated', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create();
    $user->assignRole($adminRole);

    $response = $this
        ->actingAs($user)
        ->from(route('admin.user-password.edit'))
        ->put(route('admin.user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.user-password.edit'));

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create();
    $user->assignRole($adminRole);

    $response = $this
        ->actingAs($user)
        ->from(route('admin.user-password.edit'))
        ->put(route('admin.user-password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasErrors('current_password')
        ->assertRedirect(route('admin.user-password.edit'));
});
