<?php

use App\Http\Controllers\Admin\Product\ProductController;
use App\Http\Controllers\Admin\Product\ProductReviewController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Order\OrderController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Admin\CouponsController;
use App\Http\Controllers\Admin\ShippingRulesController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\SellerProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SubscriberController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Admin\Category\CategoryController;
use \App\Http\Controllers\Admin\Category\SubCategoryController;
use \App\Http\Controllers\Admin\Category\ChildCategoryController;
use \App\Http\Controllers\Admin\BrandController;
use \App\Http\Controllers\Admin\ImportProductsController;

//Дашборд
Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::post('vendors/{vendor}/approve', [DashboardController::class, 'approveVendor'])->name('vendor.approve');
Route::delete('vendors/{vendor}/reject', [DashboardController::class, 'rejectVendor'])->name('vendor.reject');
Route::post('products/{product}/approve', [DashboardController::class, 'approveProduct'])->name('product.approve');
Route::post('reviews/{productReview}/approve', [DashboardController::class, 'approveReview'])->name('review.approve');
Route::delete('reviews/{productReview}', [DashboardController::class, 'deleteReview'])->name('review.delete');

//Категории
Route::resource('category', CategoryController::class);
Route::patch('/category/{category}/status', [CategoryController::class, 'toggleStatus'])
    ->name('category.toggle-status');

//Подкатегории
Route::resource('sub-category', SubCategoryController::class);
Route::patch('/sub-category/{subCategory}/status', [SubCategoryController::class, 'toggleStatus'])
    ->name('sub-category.toggle-status');

//Дочерняя категория
Route::resource('child-category', ChildCategoryController::class);
Route::patch('/child-category/{childCategory}/status', [ChildCategoryController::class, 'toggleStatus'])
    ->name('child-category.toggle-status');

//Бренд
Route::resource('brand', BrandController::class);
Route::patch('/brand/{brand}/status', [BrandController::class, 'toggleStatus'])->name('brand.toggle-status');
Route::patch('/brand/{brand}/is_featured', [BrandController::class, 'toggleFeature'])->name('brand.toggle-feature');

//Товары
Route::resource('product', ProductController::class);
Route::patch('/product/{product}/status', [ProductController::class, 'toggleStatus'])->name('product.toggle-status');
Route::patch('/product/{product}/field', [ProductController::class, 'updateField'])->name('product.update-field');

//Продукты продавцов
Route::get('seller-products', [SellerProductController::class, 'index'])->name('seller-product.index');
Route::patch('/seller-products/{product}/approval', [SellerProductController::class, 'toggleApproval'])->name('seller-product.toggle-approval');
Route::patch('/seller-products/{product}/status', [SellerProductController::class, 'toggleStatus'])->name('seller-product.toggle-status');

//Оценка продукта
Route::get('review', [ProductReviewController::class, 'index'])->name('review.index');
Route::patch('/review/{review}/status', [ProductReviewController::class, 'toggleStatus'])->name('review.toggle-status');
Route::delete('/review/{review}', [ProductReviewController::class, 'destroy'])->name('review.destroy');

//Заказы
Route::get('order', [OrderController::class, 'index'])->name('order.index');
Route::get('order/{order}', [OrderController::class, 'show'])->name('order.show');
Route::patch('order/{order}/status', [OrderController::class, 'updateStatus'])->name('order.update-status');
Route::patch('order/{order}/payment', [OrderController::class, 'updatePaymentStatus'])->name('order.update-payment');
Route::delete('order/{order}', [OrderController::class, 'destroy'])->name('order.destroy');

//Распродажа
Route::get('flash-sale', [FlashSaleController::class, 'index'])->name('flash-sale.index');
Route::post('flash-sale', [FlashSaleController::class, 'store'])->name('flash-sale.store');
Route::put('flash-sale/{flashSale}', [FlashSaleController::class, 'update'])->name('flash-sale.update');
Route::patch('flash-sale/{flashSale}/status', [FlashSaleController::class, 'toggleStatus'])->name('flash-sale.toggle-status');
Route::delete('flash-sale/{flashSale}', [FlashSaleController::class, 'destroy'])->name('flash-sale.destroy');

//Купоны
Route::get('coupon', [CouponsController::class, 'index'])->name('coupon.index');
Route::post('coupon', [CouponsController::class, 'store'])->name('coupon.store');
Route::put('coupon/{coupon}', [CouponsController::class, 'update'])->name('coupon.update');
Route::patch('coupon/{coupon}/status', [CouponsController::class, 'toggleStatus'])->name('coupon.toggle-status');
Route::delete('coupon/{coupon}', [CouponsController::class, 'destroy'])->name('coupon.destroy');

//Правило доставки
Route::get('shipping-rule', [ShippingRulesController::class, 'index'])->name('shipping-rule.index');
Route::post('shipping-rule', [ShippingRulesController::class, 'store'])->name('shipping-rule.store');
Route::put('shipping-rule/{shippingRule}', [ShippingRulesController::class, 'update'])->name('shipping-rule.update');
Route::patch('shipping-rule/{shippingRule}/status', [ShippingRulesController::class, 'toggleStatus'])->name('shipping-rule.toggle-status');
Route::delete('shipping-rule/{shippingRule}', [ShippingRulesController::class, 'destroy'])->name('shipping-rule.destroy');

//Слайдер
Route::get('slider', [SliderController::class, 'index'])->name('slider.index');
Route::post('slider', [SliderController::class, 'store'])->name('slider.store');
Route::put('slider/{slider}', [SliderController::class, 'update'])->name('slider.update');
Route::patch('slider/{slider}/status', [SliderController::class, 'toggleStatus'])->name('slider.toggle-status');
Route::delete('slider/{slider}', [SliderController::class, 'destroy'])->name('slider.destroy');

//Пользователи
Route::get('user', [UserController::class, 'index'])->name('user.index');
Route::patch('user/{user}/active', [UserController::class, 'toggleActive'])->name('user.toggle-active');

//Рассылки
Route::get('subscriber', [SubscriberController::class, 'index'])->name('subscriber.index');
Route::delete('subscriber/{subscriber}', [SubscriberController::class, 'destroy'])->name('subscriber.destroy');

//Импорт Товаров
Route::get('/products/import', [ImportProductsController::class, 'page'])->name('products.import.page');
Route::post('/products/import', [ImportProductsController::class, 'import'])
    ->name('products.import');
