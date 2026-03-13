<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

it('allows admin to clear sku via inline field update', function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $category = Category::create([
        'name' => 'Electronics',
        'slug' => 'electronics-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::create([
        'name' => 'Acme',
        'slug' => 'acme-'.Str::lower(Str::random(6)),
        'logo' => 'brands/acme.png',
        'status' => true,
        'is_featured' => true,
    ]);

    $product = Product::create([
        'name' => 'Acme Speaker',
        'code' => 8101,
        'slug' => 'acme-speaker',
        'thumb_image' => 'products/thumb.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 12,
        'short_description' => 'Short description',
        'long_description' => 'Long description',
        'price' => 99.99,
        'sku' => 'ACME-8101',
        'status' => true,
        'is_approved' => true,
    ]);

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.product.update-field', $product), [
            'field' => 'sku',
            'value' => '',
        ]);

    $response->assertRedirect();

    $product->refresh();

    expect($product->sku)->toBeNull();
});

it('rejects unsupported inline product fields', function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $category = Category::create([
        'name' => 'Computers',
        'slug' => 'computers-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::create([
        'name' => 'Globex',
        'slug' => 'globex-'.Str::lower(Str::random(6)),
        'logo' => 'brands/globex.png',
        'status' => true,
        'is_featured' => true,
    ]);

    $product = Product::create([
        'name' => 'Globex Laptop',
        'code' => 8102,
        'slug' => 'globex-laptop',
        'thumb_image' => 'products/thumb.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 5,
        'short_description' => 'Short description',
        'long_description' => 'Long description',
        'price' => 1200,
        'status' => true,
        'is_approved' => true,
    ]);

    $this->actingAs($admin)->patch(route('admin.product.update-field', $product), [
        'field' => 'name',
        'value' => 'Hijacked name',
    ])->assertSessionHasErrors('field');
});

it('rejects negative quantity via inline product field update', function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $category = Category::create([
        'name' => 'Audio',
        'slug' => 'audio-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::create([
        'name' => 'Acme Audio',
        'slug' => 'acme-audio-'.Str::lower(Str::random(6)),
        'logo' => 'brands/acme-audio.png',
        'status' => true,
        'is_featured' => true,
    ]);

    $product = Product::create([
        'name' => 'Acme Headphones',
        'code' => 8103,
        'slug' => 'acme-headphones',
        'thumb_image' => 'products/thumb.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 8,
        'short_description' => 'Short description',
        'long_description' => 'Long description',
        'price' => 79.99,
        'status' => true,
        'is_approved' => true,
    ]);

    $this->actingAs($admin)->patch(route('admin.product.update-field', $product), [
        'field' => 'qty',
        'value' => -1,
    ])->assertSessionHasErrors('value');
});
