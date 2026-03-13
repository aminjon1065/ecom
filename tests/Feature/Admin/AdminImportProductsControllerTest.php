<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

it('validates import products file type', function () {
    $admin = createImportAdmin();

    $this->actingAs($admin)->post(route('admin.products.import'), [
        'file' => UploadedFile::fake()->create('products.csv', 10, 'text/csv'),
    ])->assertSessionHasErrors('file');
});

function createImportAdmin(): User
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}
