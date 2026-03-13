<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Support\Str;

it('forbids submitting review for users who have not purchased product', function () {
    $user = User::factory()->create();
    $product = createReviewableProduct(2001);

    $response = $this->actingAs($user)->post(route('products.review', $product), [
        'rating' => 5,
        'review' => 'Great product',
    ]);

    $response->assertForbidden();

    expect(ProductReview::query()->count())->toBe(0);
});

it('allows purchased user to submit and update review in pending moderation state', function () {
    $user = User::factory()->create();
    $product = createReviewableProduct(2002);

    $order = Order::query()->create([
        'invoice_id' => 500001,
        'transaction_id' => 'TXN-REVIEW-1',
        'user_id' => $user->id,
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

    $createResponse = $this->actingAs($user)->post(route('products.review', $product), [
        'rating' => 4,
        'review' => 'Good first impression',
    ]);

    $createResponse->assertRedirect();

    expect(ProductReview::query()->count())->toBe(1);

    $review = ProductReview::query()->firstOrFail();

    expect($review->user_id)->toBe($user->id)
        ->and($review->product_id)->toBe($product->id)
        ->and($review->rating)->toBe(4)
        ->and($review->review)->toBe('Good first impression')
        ->and($review->status)->toBeFalse()
        ->and($review->verified_purchase)->toBeTrue();

    $updateResponse = $this->actingAs($user)->post(route('products.review', $product), [
        'rating' => 5,
        'review' => 'Updated final review',
    ]);

    $updateResponse->assertRedirect();

    expect(ProductReview::query()->count())->toBe(1)
        ->and($review->fresh()->rating)->toBe(5)
        ->and($review->fresh()->review)->toBe('Updated final review')
        ->and($review->fresh()->status)->toBeFalse()
        ->and($review->fresh()->verified_purchase)->toBeTrue();
});

function createReviewableProduct(int $code): Product
{
    $category = Category::query()->create([
        'name' => 'Review Category '.Str::random(4),
        'slug' => 'review-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Review Brand '.Str::random(4),
        'slug' => 'review-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Review Product '.$code,
        'code' => $code,
        'slug' => 'review-product-'.$code,
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 10,
        'short_description' => 'Short',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 100,
        'status' => true,
        'is_approved' => true,
    ]);
}
