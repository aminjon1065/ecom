<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

it('shows review on product page only after admin moderation approval', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $adminRole = Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $buyer = User::factory()->create();
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $product = createModeratedProduct(5101);

    $order = Order::query()->create([
        'invoice_id' => 840001,
        'transaction_id' => 'TXN-MOD-1',
        'user_id' => $buyer->id,
        'grand_total' => 100,
        'subtotal' => 100,
        'discount_total' => 0,
        'shipping_total' => 0,
        'grand_total' => 100,
        'product_quantity' => 1,
        'payment_method' => 'cash',
        'payment_status' => false,
        'coupon_code' => null,
        'coupon_code' => null,
        'order_status' => 'delivered',
    ]);

    OrderProduct::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 100,
        'discount_amount' => 0,
        'line_total' => 100,
        'product_name' => $product->name,
        'product_sku' => $product->sku,
    ]);

    $this->actingAs($buyer)->post(route('products.review', $product), [
        'rating' => 5,
        'review' => 'Moderation check review',
    ])->assertRedirect();

    $review = ProductReview::query()->firstOrFail();

    expect($review->status)->toBeFalse();

    $beforeApproval = $this->get(route('products.show', $product->slug));

    $beforeApproval->assertSuccessful();
    $beforeApproval->assertInertia(fn (Assert $page) => $page
        ->component('client/products/show')
        ->has('reviews', 0)
    );

    $this->actingAs($admin)
        ->patch(route('admin.review.toggle-status', $review))
        ->assertRedirect();

    $afterApproval = $this->get(route('products.show', $product->slug));

    $afterApproval->assertSuccessful();
    $afterApproval->assertInertia(fn (Assert $page) => $page
        ->component('client/products/show')
        ->has('reviews', 1)
        ->where('reviews.0.id', $review->id)
        ->where('reviews.0.review', 'Moderation check review')
        ->where('reviews.0.verified_purchase', true)
    );
});

function createModeratedProduct(int $code): Product
{
    $category = Category::query()->create([
        'name' => 'Moderation Category '.Str::random(4),
        'slug' => 'moderation-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Moderation Brand '.Str::random(4),
        'slug' => 'moderation-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Moderation Product '.$code,
        'code' => $code,
        'slug' => 'moderation-product-'.$code,
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 5,
        'short_description' => 'Short',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 100,
        'status' => true,
        'is_approved' => true,
    ]);
}
