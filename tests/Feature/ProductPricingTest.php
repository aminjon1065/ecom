<?php

use App\Models\Product;

it('returns offer price when offer is active', function () {
    $product = new Product([
        'price' => 200,
        'offer_price' => 150,
        'offer_start_date' => now()->subDay(),
        'offer_end_date' => now()->addDay(),
    ]);

    expect($product->hasActiveOffer())->toBeTrue()
        ->and($product->effectivePrice())->toBe(150.0)
        ->and($product->savingsAmount())->toBe(50.0);
});

it('returns regular price when offer is inactive', function () {
    $product = new Product([
        'price' => 200,
        'offer_price' => 150,
        'offer_start_date' => now()->addDay(),
        'offer_end_date' => now()->addWeek(),
    ]);

    expect($product->hasActiveOffer())->toBeFalse()
        ->and($product->effectivePrice())->toBe(200.0)
        ->and($product->savingsAmount())->toBe(0.0);
});
