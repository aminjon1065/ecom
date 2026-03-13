<?php

use App\Models\Brand;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

it('validates brand store logo as required', function () {
    $admin = createSimpleCrudAdmin();

    $this->actingAs($admin)->post(route('admin.brand.store'), [
        'name' => 'Brand without logo',
        'status' => true,
        'is_featured' => true,
    ])->assertSessionHasErrors('logo');
});

it('creates a slider through the admin form request', function () {
    Storage::fake('public');

    $admin = createSimpleCrudAdmin();

    $this->actingAs($admin)->post(route('admin.slider.store'), [
        'banner' => UploadedFile::fake()->image('slider.jpg', 1400, 600),
        'type' => 'hero',
        'title' => 'Main slider',
        'starting_price' => '$99',
        'btn_url' => '/products/main-slider',
        'serial' => 1,
        'status' => true,
    ])->assertRedirect();

    $slider = Slider::query()->firstOrFail();

    expect($slider->title)->toBe('Main slider')
        ->and($slider->status)->toBeTrue()
        ->and($slider->banner)->toStartWith('sliders/');

    Storage::disk('public')->assertExists($slider->banner);
});

it('validates slider serial uniqueness on update', function () {
    $admin = createSimpleCrudAdmin();

    $firstSlider = Slider::query()->create([
        'banner' => 'sliders/first.webp',
        'type' => 'hero',
        'title' => 'First slider',
        'starting_price' => '$10',
        'btn_url' => '/first',
        'serial' => 1,
        'status' => true,
    ]);

    $secondSlider = Slider::query()->create([
        'banner' => 'sliders/second.webp',
        'type' => 'hero',
        'title' => 'Second slider',
        'starting_price' => '$20',
        'btn_url' => '/second',
        'serial' => 2,
        'status' => true,
    ]);

    $this->actingAs($admin)->put(route('admin.slider.update', $secondSlider), [
        'type' => 'hero',
        'title' => 'Updated second slider',
        'starting_price' => '$25',
        'btn_url' => '/second',
        'serial' => $firstSlider->serial,
        'status' => true,
    ])->assertSessionHasErrors('serial');
});

it('creates a flash sale through the admin form request', function () {
    $admin = createSimpleCrudAdmin();
    $product = createSimpleCrudProduct();

    $this->actingAs($admin)->post(route('admin.flash-sale.store'), [
        'product_id' => $product->id,
        'end_date' => now()->addDays(2)->toDateString(),
        'status' => true,
        'show_at_main' => true,
    ])->assertRedirect();

    $flashSale = FlashSale::query()->firstOrFail();

    expect($flashSale->product_id)->toBe($product->id)
        ->and($flashSale->status)->toBeTrue()
        ->and($flashSale->show_at_main)->toBeTrue();
});

it('validates flash sale store end date as future date', function () {
    $admin = createSimpleCrudAdmin();
    $product = createSimpleCrudProduct();

    $this->actingAs($admin)->post(route('admin.flash-sale.store'), [
        'product_id' => $product->id,
        'end_date' => now()->toDateString(),
        'status' => true,
        'show_at_main' => true,
    ])->assertSessionHasErrors('end_date');
});

function createSimpleCrudAdmin(): User
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

function createSimpleCrudProduct(): Product
{
    $brand = Brand::query()->create([
        'name' => 'Flash Sale Brand '.Str::random(4),
        'slug' => 'flash-sale-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    $category = \App\Models\Category::query()->create([
        'name' => 'Flash Sale Category '.Str::random(4),
        'slug' => 'flash-sale-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    return Product::query()->create([
        'name' => 'Flash Sale Product '.Str::random(4),
        'code' => random_int(100000, 999999),
        'slug' => 'flash-sale-product-'.Str::lower(Str::random(8)),
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 10,
        'short_description' => 'Short description',
        'long_description' => 'Long description',
        'price' => 120,
        'status' => true,
        'is_approved' => true,
    ]);
}
