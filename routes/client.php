<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\PhoneAuthController;
use App\Http\Controllers\Auth\TelegramAuthController;
use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\NewsletterSubscriberController;
use App\Http\Controllers\Client\OrderTrackingController;
use App\Http\Controllers\Client\PriceAlertController;
use App\Http\Controllers\Client\ProductController;
use App\Http\Controllers\Client\UserAddressController;
use App\Http\Controllers\Client\WishlistController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Telegram auth (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/auth/telegram', [TelegramAuthController::class, 'show'])->name('auth.telegram');
    Route::post('/auth/telegram/callback', [TelegramAuthController::class, 'callback'])->name('auth.telegram.callback');

    // Google auth
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
    Route::get('/auth/google/phone', [GoogleAuthController::class, 'showPhone'])->name('auth.google.phone');
    Route::post('/auth/google/phone', [GoogleAuthController::class, 'storePhone'])->name('auth.google.phone.store');

    // Phone (OTP) auth — throttle OTP sends to 5 per minute per IP
    Route::get('/auth/phone', [PhoneAuthController::class, 'showLogin'])->name('auth.phone');
    Route::post('/auth/phone/otp', [PhoneAuthController::class, 'sendOtp'])
        ->middleware('throttle:5,1')
        ->name('auth.phone.otp');
    Route::post('/auth/phone/verify', [PhoneAuthController::class, 'verifyOtp'])
        ->middleware('throttle:10,1')
        ->name('auth.phone.verify');
});

// Public routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::post('/newsletter', [NewsletterSubscriberController::class, 'store'])->name('newsletter.store');
Route::get('/newsletter/verify/{token}', [NewsletterSubscriberController::class, 'verify'])->name('newsletter.verify');
Route::get('/delivery', fn () => Inertia::render('client/info/show', [
    'title' => 'Доставка',
    'description' => 'Условия доставки и сроки получения заказов.',
    'sections' => [
        [
            'title' => 'Сроки доставки',
            'items' => [
                'По городу заказы обычно доставляются в течение 1-2 дней.',
                'По регионам сроки зависят от адреса и выбранной службы доставки.',
                'После оформления заказа менеджер связывается для подтверждения деталей.',
            ],
        ],
        [
            'title' => 'Стоимость доставки',
            'items' => [
                'Стоимость рассчитывается при оформлении заказа.',
                'Финальная сумма зависит от адреса, веса и выбранного способа доставки.',
            ],
        ],
        [
            'title' => 'Получение заказа',
            'items' => [
                'Перед отправкой мы проверяем комплектность и состояние товара.',
                'При получении рекомендуем проверить упаковку и сам товар.',
            ],
        ],
    ],
]))->name('info.delivery');
Route::get('/payment', fn () => Inertia::render('client/info/show', [
    'title' => 'Оплата',
    'description' => 'Доступные способы оплаты и порядок подтверждения платежа.',
    'sections' => [
        [
            'title' => 'Способы оплаты',
            'items' => [
                'Оплата доступна наличными при получении или банковской картой.',
                'Доступные способы оплаты зависят от выбранного способа доставки.',
            ],
        ],
        [
            'title' => 'Подтверждение платежа',
            'items' => [
                'После успешной оплаты статус заказа обновляется в личном кабинете.',
                'Если платёж не прошёл, вы сможете повторить оформление заказа.',
            ],
        ],
        [
            'title' => 'Безопасность',
            'items' => [
                'Мы не храним данные банковских карт в интерфейсе магазина.',
                'Для вопросов по оплате можно обратиться в поддержку магазина.',
            ],
        ],
    ],
]))->name('info.payment');
Route::get('/returns', fn () => Inertia::render('client/info/show', [
    'title' => 'Возврат',
    'description' => 'Порядок возврата товара и условия обращения.',
    'sections' => [
        [
            'title' => 'Условия возврата',
            'items' => [
                'Возврат возможен в рамках правил магазина и действующего законодательства.',
                'Товар должен сохранить товарный вид, комплектность и документы о покупке.',
            ],
        ],
        [
            'title' => 'Как оформить возврат',
            'items' => [
                'Свяжитесь с поддержкой и сообщите номер заказа.',
                'Мы уточним статус заявки и дальнейшие шаги по возврату товара.',
            ],
        ],
        [
            'title' => 'Сроки рассмотрения',
            'items' => [
                'Срок обработки зависит от причины обращения и состояния товара.',
                'После подтверждения возврата мы сообщим дальнейший порядок компенсации.',
            ],
        ],
    ],
]))->name('info.returns');
Route::get('/track-order', [OrderTrackingController::class, 'index'])->name('track-order');
Route::get('/api/search', [ProductController::class, 'search'])->name('api.search');
Route::get('/api/search/popular', [ProductController::class, 'popularSearches'])->name('api.search.popular');

// Auth-required routes
Route::middleware(['auth'])->group(function () {
    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/move-to-cart', [WishlistController::class, 'moveAllToCart'])->name('wishlist.move-to-cart');
    Route::delete('/wishlist/{wishlist}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon.apply');
    Route::delete('/checkout/coupon', [CheckoutController::class, 'removeCoupon'])->name('checkout.coupon.remove');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store')->middleware('throttle:5,1');

    // Reviews
    Route::post('/products/{product}/review', [ProductController::class, 'submitReview'])->name('products.review');
    Route::post('/products/{product}/price-alert', [PriceAlertController::class, 'store'])->name('price-alerts.store');
    Route::delete('/products/{product}/price-alert', [PriceAlertController::class, 'destroy'])->name('price-alerts.destroy');

    // Account
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [AccountController::class, 'orderShow'])->name('orders.show');
        Route::get('/orders/{order}/invoice', [AccountController::class, 'downloadInvoice'])->name('orders.invoice');
        Route::post('/orders/{order}/repeat', [AccountController::class, 'repeatOrder'])->name('orders.repeat');
        Route::patch('/orders/{order}/cancel', [AccountController::class, 'cancelOrder'])->name('orders.cancel');
        Route::get('/addresses', [AccountController::class, 'addresses'])->name('addresses');
        Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
        Route::put('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [AccountController::class, 'updatePassword'])->name('password.update');

        // Address management
        Route::post('/addresses', [UserAddressController::class, 'store'])->name('addresses.store');
        Route::put('/addresses/{address}', [UserAddressController::class, 'update'])->name('addresses.update');
        Route::delete('/addresses/{address}', [UserAddressController::class, 'destroy'])->name('addresses.destroy');
    });
});
