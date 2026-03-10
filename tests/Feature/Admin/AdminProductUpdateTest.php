<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function makeAdminForProductUpdate(): User
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

it('updates product with plain text long description from admin panel', function () {
    $admin = makeAdminForProductUpdate();

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
        'name' => 'Legacy Product',
        'code' => 9201,
        'slug' => 'legacy-product',
        'thumb_image' => 'products/thumb.png',
        'category_id' => $category->id,
        'sub_category_id' => null,
        'child_category_id' => null,
        'brand_id' => $brand->id,
        'qty' => 10,
        'short_description' => 'Short description',
        'long_description' => 'Plain text long description',
        'sku' => 'LEGACY-9201',
        'price' => 120.50,
        'cost_price' => 95.00,
        'offer_price' => 99.99,
        'offer_start_date' => now()->toDateString(),
        'offer_end_date' => now()->addDays(2)->toDateString(),
        'product_type' => 'top',
        'status' => true,
        'is_approved' => true,
        'seo_title' => 'Legacy title',
        'seo_description' => 'Legacy description',
    ]);

    $response = $this->actingAs($admin)->put(route('admin.product.update', $product), [
        'name' => 'Legacy Product Updated',
        'code' => 9201,
        'sku' => 'LEGACY-9201',
        'qty' => 15,
        'price' => 130.50,
        'cost_price' => 100,
        'offer_price' => 110,
        'offer_start_date' => now()->toDateString(),
        'offer_end_date' => now()->addDays(3)->toDateString(),
        'category_id' => $category->id,
        'sub_category_id' => null,
        'child_category_id' => null,
        'brand_id' => $brand->id,
        'short_description' => 'Updated short description',
        'long_description' => 'Still plain text description',
        'seo_title' => 'Updated SEO title',
        'seo_description' => 'Updated SEO description',
        'product_type' => 'top',
        'status' => true,
        'is_approved' => true,
    ]);

    $response->assertRedirect(route('admin.product.index'));
    $response->assertSessionHasNoErrors();

    $product->refresh();

    expect($product->name)->toBe('Legacy Product Updated')
        ->and($product->long_description)->toBe('Still plain text description')
        ->and($product->price)->toBe(130.5);
});

it('updates a legacy product type row from admin panel using canonical enum value', function () {
    $admin = makeAdminForProductUpdate();

    $category = Category::create([
        'name' => 'Phones',
        'slug' => 'phones-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::create([
        'name' => 'Globex',
        'slug' => 'globex-'.Str::lower(Str::random(6)),
        'logo' => 'brands/globex.png',
        'status' => true,
        'is_featured' => true,
    ]);

    DB::table('products')->insert([
        'name' => 'Legacy Arrival Product',
        'code' => 9202,
        'slug' => 'legacy-arrival-product',
        'thumb_image' => 'products/thumb.png',
        'category_id' => $category->id,
        'sub_category_id' => null,
        'child_category_id' => null,
        'brand_id' => $brand->id,
        'qty' => 6,
        'short_description' => 'Short description',
        'long_description' => 'Plain text long description',
        'sku' => 'LEGACY-9202',
        'price' => 180,
        'cost_price' => 120,
        'offer_price' => null,
        'offer_start_date' => null,
        'offer_end_date' => null,
        'product_type' => 'new_arrival',
        'status' => true,
        'is_approved' => true,
        'seo_title' => 'Legacy title',
        'seo_description' => 'Legacy description',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $product = Product::query()->where('code', 9202)->firstOrFail();

    $response = $this->actingAs($admin)->put(route('admin.product.update', $product), [
        'name' => 'Legacy Arrival Product Updated',
        'code' => 9202,
        'sku' => 'LEGACY-9202',
        'qty' => 8,
        'price' => 199.99,
        'cost_price' => 130,
        'offer_price' => null,
        'offer_start_date' => null,
        'offer_end_date' => null,
        'category_id' => $category->id,
        'sub_category_id' => null,
        'child_category_id' => null,
        'brand_id' => $brand->id,
        'short_description' => 'Updated short description',
        'long_description' => 'Updated plain text long description',
        'seo_title' => 'Updated SEO title',
        'seo_description' => 'Updated SEO description',
        'product_type' => 'new',
        'status' => true,
        'is_approved' => true,
    ]);

    $response->assertRedirect(route('admin.product.index'));
    $response->assertSessionHasNoErrors();

    $product->refresh();

    expect($product->name)->toBe('Legacy Arrival Product Updated')
        ->and($product->product_type?->value)->toBe('new')
        ->and($product->price)->toBe(199.99);
});
