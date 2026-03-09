<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

test('profile page is displayed', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create();
    $user->assignRole($adminRole);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create();
    $user->assignRole($adminRole);

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create();
    $user->assignRole($adminRole);

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create();
    $user->assignRole($adminRole);

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect(App\Models\User::find($user->id))->toBeNull();
});

test('correct password must be provided to delete account', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create();
    $user->assignRole($adminRole);

    $response = $this
        ->actingAs($user)
        ->from(route('admin.profile.edit'))
        ->delete(route('admin.profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('admin.profile.edit'));

    expect($user->fresh())->not->toBeNull();
});
