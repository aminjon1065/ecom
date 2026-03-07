<?php

use App\Models\Coupons;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function makeAdminForCouponCrud(): User
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

it('creates coupon from admin panel with synced coupon engine fields', function () {
    $admin = makeAdminForCouponCrud();

    $response = $this->actingAs($admin)->post(route('admin.coupon.store'), [
        'name' => 'Spring Sale',
        'code' => 'spring20',
        'quantity' => 15,
        'max_use' => 30,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(5)->toDateString(),
        'discount_type' => 'percent',
        'discount' => 20,
        'status' => true,
    ]);

    $response->assertRedirect();

    $coupon = Coupons::query()->where('code', 'SPRING20')->firstOrFail();

    expect($coupon->usage_limit)->toBe(30)
        ->and($coupon->is_active)->toBeTrue()
        ->and($coupon->starts_at)->not->toBeNull()
        ->and($coupon->ends_at)->not->toBeNull();
});

it('renders coupon create and edit pages in admin panel', function () {
    $admin = makeAdminForCouponCrud();
    $coupon = Coupons::query()->create([
        'name' => 'Editable coupon',
        'code' => 'EDIT10',
        'quantity' => 10,
        'max_use' => 20,
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addDay()->toDateString(),
        'discount_type' => 'fixed',
        'discount' => 10,
        'status' => true,
        'is_active' => true,
        'total_used' => 0,
    ]);

    $createResponse = $this->actingAs($admin)->get(route('admin.coupon.create'));
    $createResponse->assertSuccessful();
    $createResponse->assertInertia(fn (Assert $page) => $page->component('admin/coupon/create'));

    $editResponse = $this->actingAs($admin)->get(route('admin.coupon.edit', $coupon));
    $editResponse->assertSuccessful();
    $editResponse->assertInertia(fn (Assert $page) => $page
        ->component('admin/coupon/edit')
        ->where('coupon.id', $coupon->id)
        ->where('coupon.code', $coupon->code)
    );
});

it('validates admin coupon payload', function () {
    $admin = makeAdminForCouponCrud();

    $response = $this->actingAs($admin)->post(route('admin.coupon.store'), [
        'name' => '',
        'code' => '',
        'quantity' => 0,
        'max_use' => 0,
        'start_date' => now()->toDateString(),
        'end_date' => now()->subDay()->toDateString(),
        'discount_type' => 'invalid',
        'discount' => -1,
    ]);

    $response->assertSessionHasErrors([
        'name',
        'code',
        'quantity',
        'max_use',
        'end_date',
        'discount_type',
        'discount',
    ]);
});

it('updates and deletes coupon from admin panel', function () {
    $admin = makeAdminForCouponCrud();
    $coupon = Coupons::query()->create([
        'name' => 'Legacy coupon',
        'code' => 'LEGACY10',
        'quantity' => 10,
        'max_use' => 20,
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addDay()->toDateString(),
        'discount_type' => 'fixed',
        'discount' => 10,
        'status' => true,
        'is_active' => true,
        'total_used' => 0,
    ]);

    $updateResponse = $this->actingAs($admin)->put(route('admin.coupon.update', $coupon), [
        'name' => 'Updated coupon',
        'code' => 'LEGACY15',
        'quantity' => 25,
        'max_use' => 60,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(7)->toDateString(),
        'discount_type' => 'fixed',
        'discount' => 15,
        'status' => false,
    ]);

    $updateResponse->assertRedirect();

    expect($coupon->fresh()->code)->toBe('LEGACY15')
        ->and($coupon->fresh()->usage_limit)->toBe(60)
        ->and($coupon->fresh()->is_active)->toBeFalse();

    $deleteResponse = $this->actingAs($admin)->delete(route('admin.coupon.destroy', $coupon));

    $deleteResponse->assertRedirect();

    expect(Coupons::query()->whereKey($coupon->id)->exists())->toBeFalse();
});
