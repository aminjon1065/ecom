<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function createVendorUser(): array
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $role = Role::query()->firstOrCreate(['name' => 'vendor', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    $vendor = Vendor::query()->create([
        'user_id' => $user->id,
        'shop_name' => 'Test Shop '.Str::random(4),
        'status' => true,
    ]);

    return [$user, $vendor];
}

function makeVendorProduct(Vendor $vendor, int $code): Product
{
    $category = Category::query()->create([
        'name' => 'VP Cat '.Str::random(4),
        'slug' => 'vp-cat-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'VP Brand '.Str::random(4),
        'slug' => 'vp-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'VP Product '.$code,
        'code' => $code,
        'slug' => 'vp-product-'.$code,
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'vendor_id' => $vendor->id,
        'qty' => 10,
        'short_description' => 'Short description',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 100.0,
        'status' => true,
        'is_approved' => true,
    ]);
}

it('vendor can create a product (pending approval)', function () {
    Storage::fake('public');

    [$user, $vendor] = createVendorUser();

    $category = Category::query()->create([
        'name' => 'Create Cat'.Str::random(3),
        'slug' => 'create-cat-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Create Brand'.Str::random(3),
        'slug' => 'create-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    $this->actingAs($user)->post(route('vendor.product.store'), [
        'name' => 'My New Product',
        'code' => 77001,
        'thumb_image' => UploadedFile::fake()->image('product.jpg', 400, 400),
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 5,
        'price' => 199.99,
        'short_description' => 'A short description here',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
    ])->assertRedirect(route('vendor.product.index'));

    $product = Product::query()->where('code', 77001)->firstOrFail();

    expect($product->vendor_id)->toBe($vendor->id)
        ->and($product->is_approved)->toBeFalse()
        ->and($product->status)->toBeTrue();
});

it('vendor can update their own product and it resets approval', function () {
    Storage::fake('public');

    [$user, $vendor] = createVendorUser();
    $product = makeVendorProduct($vendor, 77002);

    $this->actingAs($user)->put(route('vendor.product.update', $product), [
        'name' => 'Updated Product Name',
        'code' => 77002,
        'category_id' => $product->category_id,
        'brand_id' => $product->brand_id,
        'qty' => 20,
        'price' => 150.0,
        'short_description' => 'Updated short description',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
    ])->assertRedirect(route('vendor.product.index'));

    $product->refresh();

    expect($product->name)->toBe('Updated Product Name')
        ->and($product->qty)->toBe(20)
        ->and($product->is_approved)->toBeFalse();
});

it('vendor cannot update another vendor product', function () {
    [$user1, $vendor1] = createVendorUser();
    [$user2, $vendor2] = createVendorUser();

    $product = makeVendorProduct($vendor1, 77003);

    $this->actingAs($user2)->put(route('vendor.product.update', $product), [
        'name' => 'Hijacked',
        'code' => 77003,
        'category_id' => $product->category_id,
        'brand_id' => $product->brand_id,
        'qty' => 1,
        'price' => 1.0,
        'short_description' => 'short',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
    ])->assertForbidden();
});

it('vendor can toggle status of their own product', function () {
    [$user, $vendor] = createVendorUser();
    $product = makeVendorProduct($vendor, 77004);

    expect($product->status)->toBeTrue();

    $this->actingAs($user)->patch(route('vendor.product.status', $product))->assertRedirect();

    expect($product->fresh()->status)->toBeFalse();
});

it('vendor can delete their own product', function () {
    Storage::fake('public');

    [$user, $vendor] = createVendorUser();
    $product = makeVendorProduct($vendor, 77005);

    $this->actingAs($user)->delete(route('vendor.product.destroy', $product))->assertRedirect();

    expect(Product::query()->find($product->id))->toBeNull();
});

it('vendor cannot delete another vendor product', function () {
    [$user1, $vendor1] = createVendorUser();
    [$user2, $vendor2] = createVendorUser();

    $product = makeVendorProduct($vendor1, 77006);

    $this->actingAs($user2)->delete(route('vendor.product.destroy', $product))->assertForbidden();

    expect(Product::query()->find($product->id))->not->toBeNull();
});
