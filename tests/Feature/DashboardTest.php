<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

test('guests are redirected to the login page', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);
    Role::query()->firstOrCreate([
        'name' => 'user',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create();
    $user->assignRole($adminRole);

    $this->actingAs($user);

    $this->get(route('admin.dashboard'))->assertOk();
});
