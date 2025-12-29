<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(): \Inertia\Response
    {
        return Inertia::render('admin/category/all-categories');
    }
}
