<?php

use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function createAuditAdmin(): User
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $role = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole($role);

    return $admin;
}

it('records audit log entry when admin creates a brand', function () {
    $admin = createAuditAdmin();

    $this->actingAs($admin)->post(route('admin.brand.store'), [
        'name' => 'Audit Brand '.Str::random(4),
        'logo' => \Illuminate\Http\UploadedFile::fake()->image('logo.png', 100, 100),
        'status' => true,
        'is_featured' => true,
    ]);

    $log = AuditLog::query()->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($admin->id)
        ->and($log->action)->toContain('POST')
        ->and($log->model_type)->toBe('App\\Models\\Brand');
});

it('records audit log entry when admin toggles brand status', function () {
    $admin = createAuditAdmin();

    $brand = Brand::query()->create([
        'name' => 'Toggle Brand '.Str::random(4),
        'slug' => 'toggle-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    $this->actingAs($admin)->patch(route('admin.brand.toggle-status', $brand));

    $log = AuditLog::query()->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($admin->id)
        ->and($log->model_type)->toBe('App\\Models\\Brand')
        ->and($log->model_id)->toBe($brand->id);
});

it('does not record audit log for GET requests', function () {
    $admin = createAuditAdmin();
    $initialCount = AuditLog::count();

    $this->actingAs($admin)->get(route('admin.brand.index'));

    expect(AuditLog::count())->toBe($initialCount);
});

it('records ip address in audit log', function () {
    $admin = createAuditAdmin();

    $category = Category::query()->create([
        'name' => 'Test Audit Cat',
        'slug' => 'test-audit-cat-'.Str::lower(Str::random(4)),
        'status' => true,
    ]);

    $this->actingAs($admin)->patch(route('admin.category.toggle-status', $category));

    $log = AuditLog::query()->latest('id')->first();
    expect($log->ip_address)->not->toBeNull();
});
