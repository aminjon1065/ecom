<?php

use App\Jobs\CheckPriceAlertsJob;
use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceAlert;
use App\Models\Product;
use App\Models\User;
use App\Notifications\PriceDroppedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

it('sends one notification when price drops and does not duplicate at same price', function () {
    Notification::fake();

    $user = User::factory()->create();
    $product = makeJobAlertProduct(7101, 150);

    $alert = PriceAlert::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'target_price' => 150,
        'is_active' => true,
    ]);

    $product->update(['price' => 130]);

    (new CheckPriceAlertsJob)->handle();

    Notification::assertSentToTimes($user, PriceDroppedNotification::class, 1);

    $alert->refresh();

    expect($alert->last_notified_price)->toBe(130.0)
        ->and($alert->notified_at)->not->toBeNull();

    (new CheckPriceAlertsJob)->handle();

    Notification::assertSentToTimes($user, PriceDroppedNotification::class, 1);

    $product->update(['price' => 120]);

    (new CheckPriceAlertsJob)->handle();

    Notification::assertSentToTimes($user, PriceDroppedNotification::class, 2);
});

function makeJobAlertProduct(int $code, float $price): Product
{
    $category = Category::query()->create([
        'name' => 'Alert Job Category '.Str::random(4),
        'slug' => 'alert-job-category-'.Str::lower(Str::random(6)),
        'status' => true,
    ]);

    $brand = Brand::query()->create([
        'name' => 'Alert Job Brand '.Str::random(4),
        'slug' => 'alert-job-brand-'.Str::lower(Str::random(6)),
        'logo' => 'brands/default.png',
        'status' => true,
        'is_featured' => true,
    ]);

    return Product::query()->create([
        'name' => 'Alert Job Product '.$code,
        'code' => $code,
        'slug' => 'alert-job-product-'.$code,
        'thumb_image' => 'products/default.png',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'qty' => 20,
        'short_description' => 'Short',
        'long_description' => '{"root":{"children":[],"type":"root","version":1}}',
        'price' => $price,
        'status' => true,
        'is_approved' => true,
    ]);
}
