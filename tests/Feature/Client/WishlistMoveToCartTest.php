<?php

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

it('moves available wishlist products to cart', function () {
    $user = User::factory()->create();

    $available = makeWishlistProduct(9951, 2, true, true);
    $unavailable = makeWishlistProduct(9952, 0, true, true);

    Wishlist::query()->create([
        'user_id' => $user->id,
        'product_id' => $available->id,
    ]);

    Wishlist::query()->create([
        'user_id' => $user->id,
        'product_id' => $unavailable->id,
    ]);

    $response = $this->actingAs($user)->post(route('wishlist.move-to-cart'));

    $response->assertRedirect(route('cart.index'));

    expect(Cart::query()->where('user_id', $user->id)->count())->toBe(1)
        ->and(Cart::query()->where('user_id', $user->id)->firstOrFail()->product_id)->toBe($available->id)
        ->and(Wishlist::query()->where('user_id', $user->id)->count())->toBe(0);
});

it('keeps wishlist unchanged when nothing can be moved to cart', function () {
    $user = User::factory()->create();

    $unavailable = makeWishlistProduct(9953, 0, true, true);

    Wishlist::query()->create([
        'user_id' => $user->id,
        'product_id' => $unavailable->id,
    ]);

    $response = $this->actingAs($user)->post(route('wishlist.move-to-cart'));

    $response->assertRedirect();

    expect(Cart::query()->where('user_id', $user->id)->count())->toBe(0)
        ->and(Wishlist::query()->where('user_id', $user->id)->count())->toBe(1);
});

it('shows wishlist summary metrics for user', function () {
    $user = User::factory()->create();

    $discounted = makeWishlistProduct(9954, 3, true, true);
    $discounted->update([
        'offer_price' => 80,
        'offer_start_date' => now()->subDay(),
        'offer_end_date' => now()->addDay(),
    ]);

    $unavailable = makeWishlistProduct(9955, 0, true, true);

    Wishlist::query()->create([
        'user_id' => $user->id,
        'product_id' => $discounted->id,
    ]);

    Wishlist::query()->create([
        'user_id' => $user->id,
        'product_id' => $unavailable->id,
    ]);

    $response = $this->actingAs($user)->get(route('wishlist.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/wishlist')
        ->where('wishlistSummary.total', 2)
        ->where('wishlistSummary.available', 1)
        ->where('wishlistSummary.out_of_stock', 1)
        ->where('wishlistSummary.potential_savings', 20)
    );
});

function makeWishlistProduct(int $code, int $qty, bool $status, bool $approved): Product
{
    $category = Category::query()->create([
        'name' => 'Wishlist Category '.Str::random(4),
        'slug' => 'wishlist-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Wishlist Brand '.Str::random(4),
        'slug' => 'wishlist-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Wishlist Product '.$code,
        'code' => $code,
        'slug' => 'wishlist-product-'.$code,
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => $qty,
        'short_description' => 'Short',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => 100,
        'status' => $status,
        'is_approved' => $approved,
    ]);
}
