<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\TelegramAuthController;
use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\NewsletterSubscriberController;
use App\Http\Controllers\Client\OrderTrackingController;
use App\Http\Controllers\Client\ProductController;
use App\Http\Controllers\Client\UserAddressController;
use App\Http\Controllers\Client\WishlistController;
use Illuminate\Support\Facades\Route;

// Telegram auth (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/auth/telegram', [TelegramAuthController::class, 'show'])->name('auth.telegram');
    Route::post('/auth/telegram/callback', [TelegramAuthController::class, 'callback'])->name('auth.telegram.callback');

    // Google auth
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
    Route::get('/auth/google/phone', [GoogleAuthController::class, 'showPhone'])->name('auth.google.phone');
    Route::post('/auth/google/phone', [GoogleAuthController::class, 'storePhone'])->name('auth.google.phone.store');
});

// Public routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::post('/newsletter', [NewsletterSubscriberController::class, 'store'])->name('newsletter.store');
Route::get('/track-order', [OrderTrackingController::class, 'index'])->name('track-order');
Route::get('/api/search', [ProductController::class, 'search'])->name('api.search');

// Auth-required routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/{wishlist}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // Reviews
    Route::post('/products/{product}/review', [ProductController::class, 'submitReview'])->name('products.review');

    // Account
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [AccountController::class, 'orderShow'])->name('orders.show');
        Route::get('/orders/{order}/invoice', [AccountController::class, 'downloadInvoice'])->name('orders.invoice');
        Route::get('/addresses', [AccountController::class, 'addresses'])->name('addresses');
        Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
        Route::put('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');

        // Address management
        Route::post('/addresses', [UserAddressController::class, 'store'])->name('addresses.store');
        Route::put('/addresses/{address}', [UserAddressController::class, 'update'])->name('addresses.update');
        Route::delete('/addresses/{address}', [UserAddressController::class, 'destroy'])->name('addresses.destroy');
    });
});
