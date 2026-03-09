<?php

use App\Models\AuditLog;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function createPageAdmin(): User
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $role = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole($role);

    return $admin;
}

it('admin can view audit log page', function () {
    $admin = createPageAdmin();

    AuditLog::query()->create([
        'user_id' => $admin->id,
        'action' => 'POST',
        'model_type' => 'App\\Models\\Brand',
        'model_id' => '1',
        'new_values' => ['name' => 'Test Brand'],
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.audit-log.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/audit-log/index')
            ->has('logs.data', 1)
            ->has('modelTypes')
            ->has('filters')
        );
});

it('admin can filter audit log by action', function () {
    $admin = createPageAdmin();

    AuditLog::query()->create([
        'user_id' => $admin->id,
        'action' => 'POST',
        'model_type' => 'App\\Models\\Brand',
        'model_id' => '1',
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
    ]);

    AuditLog::query()->create([
        'user_id' => $admin->id,
        'action' => 'DELETE',
        'model_type' => 'App\\Models\\Brand',
        'model_id' => '2',
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.audit-log.index', ['action' => 'DELETE']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('logs.data', 1)
            ->where('logs.data.0.action', 'DELETE')
        );
});

it('non-admin cannot access audit log page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.audit-log.index'))
        ->assertForbidden();
});

it('guest is redirected from audit log page', function () {
    $this->get(route('admin.audit-log.index'))
        ->assertRedirect();
});
