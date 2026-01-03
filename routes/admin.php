<?php

use App\Http\Controllers\Admin\Product\ProductController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use \App\Http\Controllers\Admin\Category\CategoryController;
use \App\Http\Controllers\Admin\Category\SubCategoryController;
use \App\Http\Controllers\Admin\Category\ChildCategoryController;
use \App\Http\Controllers\Admin\BrandController;
use \App\Http\Controllers\Admin\ImportProductsController;

Route::get('dashboard', function () {
    return Inertia::render('admin/dashboard');
})->name('dashboard');
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


//Импорт Товаров
Route::get('/products/import', [ImportProductsController::class, 'page'])->name('products.import.page');
Route::post('/products/import', [ImportProductsController::class, 'import'])
    ->name('products.import');
