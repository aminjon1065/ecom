<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleVendor = Role::create(['name' => 'vendor']);
        $roleUser = Role::create(['name' => 'user']);

        // Create Permissions (Example permissions, you can add more)
        // $permissionEditProduct = Permission::create(['name' => 'edit product']);

        // Assign Permissions to Roles (Example)
        // $roleVendor->givePermissionTo($permissionEditProduct);
    }
}
