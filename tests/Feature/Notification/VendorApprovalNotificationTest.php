<?php

use App\Models\User;
use App\Models\Vendor;
use App\Notifications\VendorApprovedNotification;
use App\Notifications\VendorRejectedNotification;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function createAdminForNotification(): User
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $adminRole = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    return $admin;
}

function createPendingVendor(): array
{
    $vendorUser = User::factory()->create();
    $vendor = Vendor::query()->create([
        'user_id' => $vendorUser->id,
        'shop_name' => 'Test Shop',
        'status' => false,
    ]);

    return [$vendorUser, $vendor];
}

it('sends approved notification to vendor user when admin approves vendor', function () {
    Notification::fake();

    $admin = createAdminForNotification();
    [$vendorUser, $vendor] = createPendingVendor();

    $this->actingAs($admin)
        ->post(route('admin.vendor.approve', $vendor))
        ->assertRedirect();

    Notification::assertSentTo(
        $vendorUser,
        VendorApprovedNotification::class,
        fn (VendorApprovedNotification $n) => $n->vendor->id === $vendor->id,
    );

    expect($vendor->fresh()->status)->toBeTrue();
});

it('sends rejected notification to vendor user when admin rejects vendor', function () {
    Notification::fake();

    $admin = createAdminForNotification();
    [$vendorUser, $vendor] = createPendingVendor();
    $vendorId = $vendor->id;

    $this->actingAs($admin)
        ->delete(route('admin.vendor.reject', $vendor))
        ->assertRedirect();

    Notification::assertSentTo(
        $vendorUser,
        VendorRejectedNotification::class,
        fn (VendorRejectedNotification $n) => $n->vendor->id === $vendorId,
    );

    expect(Vendor::query()->find($vendorId))->toBeNull();
});

it('does not send approved notification when vendor approval fails validation', function () {
    Notification::fake();

    $admin = createAdminForNotification();

    $this->actingAs($admin)
        ->post(route('admin.vendor.approve', ['vendor' => 999999]))
        ->assertNotFound();

    Notification::assertNothingSent();
});
