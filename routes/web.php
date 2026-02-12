<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;


Route::get('/', [\App\Http\Controllers\Client\HomeController::class, 'home'])->name('home');


Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        require __DIR__ . '/admin.php';
        require __DIR__ . '/settings.php';
    });
require __DIR__ . '/client.php';
require __DIR__ . '/vendor.php';
