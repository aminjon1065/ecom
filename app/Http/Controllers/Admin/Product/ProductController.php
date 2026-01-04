<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{

    public function index(Request $request)
    {
        $products = Product::query()
            ->select([
                'id',
                'name',
                'thumb_image',
                'price',
                'sku',
                'qty',
                'code',
                'status',
            ])
            ->with([
                'category:id,name',
                'brand:id,name',
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/product/index', [
            'products' => $products,
        ]);
    }

    public function toggleStatus(Product $product): \Illuminate\Http\RedirectResponse
    {
        $product->update([
            'status' => !$product->status,
        ]);

        return redirect()->back();
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
