<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use \App\Http\Controllers\Admin\Category\CategoryController;


Route::get('dashboard', function () {
    return Inertia::render('admin/dashboard');
})->name('dashboard');
Route::resource('category', CategoryController::class);
