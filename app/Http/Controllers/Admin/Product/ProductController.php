<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\SubCategory;
use Inertia\Inertia;

class ProductController extends Controller
{

    public function index()
    {
        return Inertia::render('admin/product/index');
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('admin/product/create', [
            'categories' => Category::select('id', 'name')->get(),
            'subCategories' => SubCategory::select('id', 'name', 'category_id')->get(),
            'childCategories' => ChildCategory::select('id', 'name', 'sub_category_id')->get(),
            'brands' => Brand::select('id', 'name')->where('status', true)->get(),
        ]);
    }
}
