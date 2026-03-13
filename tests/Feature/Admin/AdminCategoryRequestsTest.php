<?php

use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

it('creates a category via the admin form request', function () {
    Storage::fake('public');

    $admin = createCategoryAdmin();

    $this->actingAs($admin)->post(route('admin.category.store'), [
        'name' => 'Electronics',
        'icon' => UploadedFile::fake()->image('icon.png', 100, 100),
        'status' => true,
    ])->assertRedirect();

    $category = Category::query()->where('name', 'Electronics')->firstOrFail();

    expect($category->slug)->toBe('electronics')
        ->and($category->status)->toBeTrue()
        ->and($category->icon)->toStartWith('categories/');

    Storage::disk('public')->assertExists($category->icon);
});

it('validates category update slug uniqueness', function () {
    $admin = createCategoryAdmin();

    $first = Category::query()->create([
        'name' => 'Phones',
        'slug' => 'phones',
        'status' => true,
    ]);

    $second = Category::query()->create([
        'name' => 'Laptops',
        'slug' => 'laptops',
        'status' => true,
    ]);

    $this->actingAs($admin)->put(route('admin.category.update', $second), [
        'name' => 'Laptops Updated',
        'slug' => $first->slug,
        'status' => true,
    ])->assertSessionHasErrors('slug');
});

it('creates a sub category via the admin form request', function () {
    $admin = createCategoryAdmin();
    $category = makeCategory('Main Category');

    $this->actingAs($admin)->post(route('admin.sub-category.store'), [
        'category_id' => $category->id,
        'name' => 'Smartphones',
        'status' => true,
    ])->assertRedirect();

    $subCategory = SubCategory::query()->where('name', 'Smartphones')->firstOrFail();

    expect($subCategory->category_id)->toBe($category->id)
        ->and($subCategory->slug)->toBe('smartphones');
});

it('validates sub category category existence', function () {
    $admin = createCategoryAdmin();

    $this->actingAs($admin)->post(route('admin.sub-category.store'), [
        'category_id' => 999999,
        'name' => 'Bad Sub Category',
        'status' => true,
    ])->assertSessionHasErrors('category_id');
});

it('creates a child category via the admin form request', function () {
    $admin = createCategoryAdmin();
    $category = makeCategory('Devices');
    $subCategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'name' => 'Phones',
        'slug' => 'phones-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $this->actingAs($admin)->post(route('admin.child-category.store'), [
        'category_id' => $category->id,
        'sub_category_id' => $subCategory->id,
        'name' => 'Android',
        'status' => true,
    ])->assertRedirect();

    $childCategory = ChildCategory::query()->where('name', 'Android')->firstOrFail();

    expect($childCategory->category_id)->toBe($category->id)
        ->and($childCategory->sub_category_id)->toBe($subCategory->id)
        ->and($childCategory->slug)->toBe('android');
});

it('validates child category sub category existence', function () {
    $admin = createCategoryAdmin();
    $category = makeCategory('Accessories');

    $this->actingAs($admin)->post(route('admin.child-category.store'), [
        'category_id' => $category->id,
        'sub_category_id' => 999999,
        'name' => 'Chargers',
        'status' => true,
    ])->assertSessionHasErrors('sub_category_id');
});

function createCategoryAdmin(): User
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

function makeCategory(string $name): Category
{
    return Category::query()->create([
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);
}
