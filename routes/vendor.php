<?php

use App\Http\Controllers\Vendor\VendorController;
use App\Http\Controllers\Vendor\VendorProductController;
use App\Http\Controllers\Vendor\VendorOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:vendor'])
    ->prefix('vendor')
    ->name('vendor.')
    ->group(function () {
        // Dashboard
        Route::get('/', [VendorController::class, 'dashboard'])->name('dashboard');

        // Profile / Shop settings
        Route::get('/profile', [VendorController::class, 'profile'])->name('profile');
        Route::put('/profile', [VendorController::class, 'updateProfile'])->name('profile.update');

        // Products
        Route::get('/products', [VendorProductController::class, 'index'])->name('product.index');
        Route::get('/products/create', [VendorProductController::class, 'create'])->name('product.create');
        Route::post('/products', [VendorProductController::class, 'store'])->name('product.store');
        Route::get('/products/{product}/edit', [VendorProductController::class, 'edit'])->name('product.edit');
        Route::put('/products/{product}', [VendorProductController::class, 'update'])->name('product.update');
        Route::delete('/products/{product}', [VendorProductController::class, 'destroy'])->name('product.destroy');
        Route::patch('/products/{product}/status', [VendorProductController::class, 'toggleStatus'])->name('product.status');

        // Orders
        Route::get('/orders', [VendorOrderController::class, 'index'])->name('order.index');
        Route::get('/orders/{order}', [VendorOrderController::class, 'show'])->name('order.show');
        Route::patch('/orders/{order}/status', [VendorOrderController::class, 'updateStatus'])->name('order.status');
    });
