<?php

use App\Imports\ProductsImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

it('imports valid rows with resolved relations and normalized description payload', function () {
    $category = Category::create([
        'name' => 'Electronics',
        'slug' => 'electronics-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $subCategory = SubCategory::create([
        'category_id' => $category->id,
        'name' => 'Speakers',
        'slug' => 'speakers-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $childCategory = ChildCategory::create([
        'category_id' => $category->id,
        'sub_category_id' => $subCategory->id,
        'name' => 'Portable',
        'slug' => 'portable-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::create([
        'name' => 'Acme',
        'slug' => 'acme-'.Str::lower(Str::random(6)),
        'logo' => 'brands/acme.png',
        'status' => true,
        'is_featured' => true,
    ]);

    $vendorUser = User::factory()->create();
    $vendor = Vendor::create([
        'user_id' => $vendorUser->id,
        'shop_name' => 'Acme Shop',
        'status' => true,
    ]);

    Product::create([
        'name' => 'Portable Speaker',
        'code' => 5010,
        'slug' => 'portable-speaker',
        'thumb_image' => 'products/thumb-old.png',
        'category_id' => $category->id,
        'sub_category_id' => $subCategory->id,
        'child_category_id' => $childCategory->id,
        'brand_id' => $brand->id,
        'vendor_id' => $vendor->id,
        'qty' => 1,
        'short_description' => 'Old',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 10,
        'status' => true,
        'is_approved' => true,
    ]);

    $import = new ProductsImport;

    $import->collection(new Collection([
        new Collection([
            'code' => 5011,
            'name' => 'Portable Speaker',
            'category' => 'Electronics',
            'sub_category' => 'Speakers',
            'child_category' => 'Portable',
            'brand' => 'Acme',
            'vendor_id' => $vendor->id,
            'thumb_image' => 'products/thumb.png',
            'sku' => 'ACME-5011',
            'qty' => 5,
            'price' => 149.99,
            'short_description' => 'Bluetooth portable',
            'long_description' => 'Plain text description',
            'status' => 'true',
            'is_approved' => '1',
        ]),
    ]));

    expect($import->errors)->toBeEmpty();

    $product = Product::query()->where('code', 5011)->firstOrFail();

    expect($product->brand_id)->toBe($brand->id)
        ->and($product->vendor_id)->toBe($vendor->id)
        ->and($product->sub_category_id)->toBe($subCategory->id)
        ->and($product->child_category_id)->toBe($childCategory->id)
        ->and($product->slug)->not->toBe('portable-speaker')
        ->and($product->slug)->toStartWith('portable-speaker-')
        ->and(json_decode($product->long_description, true))->toHaveKey('root');
});

it('collects row errors and continues importing subsequent rows', function () {
    $category = Category::create([
        'name' => 'Electronics',
        'slug' => 'electronics-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $subCategory = SubCategory::create([
        'category_id' => $category->id,
        'name' => 'Speakers',
        'slug' => 'speakers-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::create([
        'name' => 'Acme',
        'slug' => 'acme-'.Str::lower(Str::random(6)),
        'logo' => 'brands/acme.png',
        'status' => true,
        'is_featured' => true,
    ]);

    $import = new ProductsImport;

    $import->collection(new Collection([
        new Collection([
            'code' => '',
            'name' => 'Broken Row',
            'category' => 'Electronics',
            'sub_category' => 'Speakers',
            'brand' => 'Acme',
            'thumb_image' => 'products/thumb-1.png',
            'price' => 10,
        ]),
        new Collection([
            'code' => 6012,
            'name' => 'Unknown Brand Row',
            'category' => 'Electronics',
            'sub_category' => 'Speakers',
            'brand' => 'Unknown',
            'thumb_image' => 'products/thumb-2.png',
            'price' => 20,
        ]),
        new Collection([
            'code' => 6013,
            'name' => 'Valid Row',
            'category' => 'Electronics',
            'sub_category' => 'Speakers',
            'brand' => $brand->name,
            'thumb_image' => 'products/thumb-3.png',
            'qty' => 2,
            'price' => 30,
        ]),
    ]));

    expect($import->errors)->toHaveCount(2)
        ->and(Product::query()->where('code', 0)->exists())->toBeFalse()
        ->and(Product::query()->where('code', 6012)->exists())->toBeFalse()
        ->and(Product::query()->where('code', 6013)->exists())->toBeTrue();

    expect($import->errors[0]['row'])->toBe(2)
        ->and($import->errors[1]['row'])->toBe(3);
});
