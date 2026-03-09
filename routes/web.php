<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\Client\HomeController::class, 'home'])->name('home');

Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/health', \App\Http\Controllers\HealthController::class)->name('health');

Route::middleware(['auth', 'verified', 'role:admin', 'admin.audit'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        require __DIR__.'/admin.php';
        require __DIR__.'/settings.php';
    });
require __DIR__.'/client.php';
require __DIR__.'/vendor.php';
