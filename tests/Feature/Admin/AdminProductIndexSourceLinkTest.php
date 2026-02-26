<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

it('includes source links in admin product table payload', function () {
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
        'code' => 5001,
        'slug' => 'acme-speaker',
        'thumb_image' => 'products/thumb.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 12,
        'short_description' => 'Short description',
        'long_description' => 'Long description',
        'price' => 99.99,
        'sku' => 'ACME-5001',
        'status' => true,
        'is_approved' => true,
        'first_source_link' => 'https://example.com/source/acme-speaker',
        'second_source_link' => 'https://example.com/source/acme-speaker-2',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.product.index'));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/product/index')
        ->has('products.data', 1)
        ->where('products.data.0.id', $product->id)
        ->where('products.data.0.first_source_link', $product->first_source_link)
        ->where('products.data.0.second_source_link', $product->second_source_link)
    );
});
