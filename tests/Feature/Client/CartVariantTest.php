<?php

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariantItem;
use App\Models\User;

// ─── helpers ─────────────────────────────────────────────────────────────────

function makeVariantProduct(int $code = 9000): Product
{
    static $seq = 9000;
    $seq++;

    $category = Category::create(['name' => "Cat $seq", 'slug' => "cat-$seq"]);
    $brand = Brand::create(['name' => "Brand $seq", 'slug' => "brand-$seq", 'logo' => 'brands/logo.png', 'status' => true]);

    return Product::create([
        'name' => "Variant Product $seq",
        'code' => $seq,
        'slug' => "variant-product-$seq",
        'thumb_image' => 'products/thumb.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 20,
        'short_description' => 'Short',
        'long_description' => 'Long',
        'price' => 100,
        'status' => true,
        'is_approved' => true,
    ]);
}

function addVariant(Product $product, string $name, float $price, bool $isDefault = false): ProductVariantItem
{
    return ProductVariantItem::create([
        'product_id' => $product->id,
        'name' => $name,
        'price' => $price,
        'is_default' => $isDefault,
        'status' => true,
    ]);
}

// ─── tests ───────────────────────────────────────────────────────────────────

it('adds a product without a variant to cart', function () {
    $user = User::factory()->create();
    $product = makeVariantProduct();

    $this->actingAs($user)
        ->post('/cart', ['product_id' => $product->id, 'quantity' => 2])
        ->assertRedirect();

    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_variant_item_id' => null,
        'quantity' => 2,
    ]);
});

it('adds different variants of the same product as separate cart slots', function () {
    $user = User::factory()->create();
    $product = makeVariantProduct();
    $variantS = addVariant($product, 'S', 95.0, true);
    $variantL = addVariant($product, 'L', 110.0);

    $this->actingAs($user)
        ->post('/cart', ['product_id' => $product->id, 'variant_id' => $variantS->id])
        ->assertRedirect();

    $this->actingAs($user)
        ->post('/cart', ['product_id' => $product->id, 'variant_id' => $variantL->id])
        ->assertRedirect();

    expect(Cart::where('user_id', $user->id)->where('product_id', $product->id)->count())->toBe(2);
    $this->assertDatabaseHas('carts', ['user_id' => $user->id, 'product_variant_item_id' => $variantS->id, 'quantity' => 1]);
    $this->assertDatabaseHas('carts', ['user_id' => $user->id, 'product_variant_item_id' => $variantL->id, 'quantity' => 1]);
});

it('increments quantity when adding same variant again', function () {
    $user = User::factory()->create();
    $product = makeVariantProduct();
    $variant = addVariant($product, 'M', 100.0, true);

    $this->actingAs($user)->post('/cart', ['product_id' => $product->id, 'variant_id' => $variant->id]);
    $this->actingAs($user)->post('/cart', ['product_id' => $product->id, 'variant_id' => $variant->id]);

    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'product_variant_item_id' => $variant->id,
        'quantity' => 2,
    ]);
    expect(Cart::where('user_id', $user->id)->where('product_id', $product->id)->count())->toBe(1);
});

it('rejects a variant_id that does not exist', function () {
    $user = User::factory()->create();
    $product = makeVariantProduct();

    $this->actingAs($user)
        ->post('/cart', ['product_id' => $product->id, 'variant_id' => 999999])
        ->assertSessionHasErrors('variant_id');
});

it('only authenticated users can add to cart', function () {
    $product = makeVariantProduct();
    $variant = addVariant($product, 'XL', 120.0);

    $this->post('/cart', ['product_id' => $product->id, 'variant_id' => $variant->id])
        ->assertRedirect('/login');
});
