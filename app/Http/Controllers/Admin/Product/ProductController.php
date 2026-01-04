<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\SubCategory;
use Inertia\Inertia;

class ProductController extends Controller
{

    public function index(): \Inertia\Response
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

    public function store(StoreProductRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();

        // THUMB
        if ($request->hasFile('thumb_image')) {
            $data['thumb_image'] = $request->file('thumb_image')
                ->store('products/thumbs', 'public');
        }
        $data['slug'] = \Str::slug($data['name']);

        // GALLERY
        if ($request->hasFile('gallery')) {
            $data['gallery'] = collect($request->file('gallery'))
                ->map(fn($file) => $file->store('products/gallery', 'public'))
                ->toArray();
        }

        try {
            $data['long_description'] = json_decode($data['long_description'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            \Log::error($e->getMessage());
        }

        Product::create($data);
        return redirect()->route('admin.product.index')->with('success', 'Продукт добавлен');
    }
}
