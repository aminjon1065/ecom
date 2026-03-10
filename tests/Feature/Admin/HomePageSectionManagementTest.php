<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\HomePageSection;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function makeHomeSectionAdmin(): User
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

function makeHomeSectionCategory(string $name): Category
{
    return Category::query()->create([
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);
}

function makeHomeSectionBrand(string $name): Brand
{
    return Brand::query()->create([
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function makeHomeSectionProduct(array $attributes): Product
{
    return Product::query()->create(array_merge([
        'name' => 'Home Product '.Str::random(4),
        'code' => random_int(1000, 9999),
        'slug' => 'home-product-'.Str::lower(Str::random(8)),
        'thumb_image' => 'products/default.png',
        'category_id' => makeHomeSectionCategory('Default '.Str::random(4))->id,
        'brand_id' => makeHomeSectionBrand('Brand '.Str::random(4))->id,
        'qty' => 10,
        'short_description' => 'Short description',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 150,
        'status' => true,
        'is_approved' => true,
    ], $attributes));
}

it('renders the home page section settings page for admins', function () {
    $admin = makeHomeSectionAdmin();

    $category = makeHomeSectionCategory('Phones');
    HomePageSection::query()->create([
        'position' => 1,
        'type' => 'category',
        'category_id' => $category->id,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.home-page-section.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/home-page-section/index')
        ->where('sections.0.type', 'category')
        ->where('sections.0.category_id', $category->id)
        ->where('categories.0.id', $category->id)
    );
});

it('updates home page sections from the admin panel', function () {
    $admin = makeHomeSectionAdmin();
    $category = makeHomeSectionCategory('Laptops');

    HomePageSection::query()->create([
        'position' => 1,
        'type' => 'best_products',
        'category_id' => null,
    ]);

    $response = $this->actingAs($admin)->put(route('admin.home-page-section.update'), [
        'sections' => [
            [
                'type' => 'category',
                'category_id' => $category->id,
            ],
            [
                'type' => 'flash_sale',
                'category_id' => null,
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.home-page-section.index'));

    $sections = HomePageSection::query()->orderBy('position')->get();

    expect($sections)->toHaveCount(2)
        ->and($sections[0]->position)->toBe(1)
        ->and($sections[0]->type)->toBe('category')
        ->and($sections[0]->category_id)->toBe($category->id)
        ->and($sections[1]->position)->toBe(2)
        ->and($sections[1]->type)->toBe('flash_sale')
        ->and($sections[1]->category_id)->toBeNull();
});

it('renders configured content blocks on the home page', function () {
    $category = makeHomeSectionCategory('Accessories');
    $saleCategory = makeHomeSectionCategory('Sale Accessories');
    $brand = makeHomeSectionBrand('Featured Brand');

    $categoryProduct = makeHomeSectionProduct([
        'name' => 'Category Product',
        'slug' => 'category-product',
        'code' => 5501,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
    ]);

    $flashSaleProduct = makeHomeSectionProduct([
        'name' => 'Flash Sale Product',
        'slug' => 'flash-sale-product',
        'code' => 5502,
        'category_id' => $saleCategory->id,
        'brand_id' => $brand->id,
    ]);

    FlashSale::query()->create([
        'product_id' => $flashSaleProduct->id,
        'end_date' => now()->addDay()->toDateString(),
        'status' => true,
        'show_at_main' => true,
    ]);

    HomePageSection::query()->create([
        'position' => 1,
        'type' => 'category',
        'category_id' => $category->id,
    ]);

    HomePageSection::query()->create([
        'position' => 2,
        'type' => 'flash_sale',
        'category_id' => null,
    ]);

    $response = $this->get(route('home'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('welcome')
        ->has('contentBlocks', 2)
        ->where('contentBlocks.0.type', 'category')
        ->where('contentBlocks.0.title', $category->name)
        ->where('contentBlocks.0.products.0.id', $categoryProduct->id)
        ->where('contentBlocks.1.type', 'flash_sale')
        ->where('contentBlocks.1.title', 'Акции и скидки')
        ->where('contentBlocks.1.products.0.id', $flashSaleProduct->id)
    );
});
