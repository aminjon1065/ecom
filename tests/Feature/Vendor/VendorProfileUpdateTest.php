<?php

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

it('vendor can update their profile through the form request', function () {
    Storage::fake('public');

    [$user, $vendor] = createProfileVendorUser();

    $this->actingAs($user)->put(route('vendor.profile.update'), [
        'shop_name' => 'Updated Shop',
        'description' => 'Updated description',
        'address' => 'Dushanbe',
        'banner' => UploadedFile::fake()->image('banner.jpg', 1200, 300),
        'facebook_url' => 'https://facebook.com/updated-shop',
        'telegram_url' => 'https://t.me/updatedshop',
        'instagram_url' => 'https://instagram.com/updatedshop',
    ])->assertRedirect();

    $vendor->refresh();

    expect($vendor->shop_name)->toBe('Updated Shop')
        ->and($vendor->address)->toBe('Dushanbe')
        ->and($vendor->banner)->toStartWith('vendors/');

    Storage::disk('public')->assertExists($vendor->banner);
});

it('vendor profile update validates social links', function () {
    [$user] = createProfileVendorUser();

    $this->actingAs($user)->put(route('vendor.profile.update'), [
        'shop_name' => 'Broken Shop',
        'facebook_url' => 'not-a-url',
    ])->assertSessionHasErrors('facebook_url');
});

function createProfileVendorUser(): array
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $vendorRole = Role::query()->firstOrCreate([
        'name' => 'vendor',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create();
    $user->assignRole($vendorRole);

    $vendor = Vendor::query()->create([
        'user_id' => $user->id,
        'shop_name' => 'Profile Shop '.Str::random(4),
        'status' => true,
    ]);

    return [$user, $vendor];
}
