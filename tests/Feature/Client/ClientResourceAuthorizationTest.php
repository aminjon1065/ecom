<?php

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Wishlist;
use Illuminate\Support\Str;

it('forbids updating another user address', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $address = UserAddress::query()->create([
        'user_id' => $owner->id,
        'address' => 'Owner address',
        'description' => 'home',
    ]);

    $response = $this->actingAs($otherUser)->put(route('account.addresses.update', $address), [
        'address' => 'Changed address',
        'description' => 'office',
    ]);

    $response->assertForbidden();
});

it('forbids deleting another user cart item', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $product = makeClientOwnedProduct(7101);

    $cart = Cart::query()->create([
        'user_id' => $owner->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($otherUser)->delete(route('cart.destroy', $cart));

    $response->assertForbidden();
});

it('forbids deleting another user wishlist item', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $product = makeClientOwnedProduct(7102);

    $wishlist = Wishlist::query()->create([
        'user_id' => $owner->id,
        'product_id' => $product->id,
    ]);

    $response = $this->actingAs($otherUser)->delete(route('wishlist.destroy', $wishlist));

    $response->assertForbidden();
});

function makeClientOwnedProduct(int $code): Product
{
    $category = Category::query()->create([
        'name' => 'Client Auth Category '.Str::random(4),
        'slug' => 'client-auth-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Client Auth Brand '.Str::random(4),
        'slug' => 'client-auth-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Client Auth Product '.$code,
        'code' => $code,
        'slug' => 'client-auth-product-'.$code,
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
