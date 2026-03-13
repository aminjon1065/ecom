<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProductFieldRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\SubCategory;
use App\Services\Product\ProductUpsertService;
use App\Services\Product\ProductWriteOptions;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductUpsertService $productUpsertService,
    ) {}

    public function index(Request $request): \Inertia\Response
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
                'category_id',
                'brand_id',
                'first_source_link',
                'second_source_link',
            ])
            ->with([
                'category:id,name',
                'brand:id,name',
            ])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($request->category_id, fn ($q, $v) => $q->where('category_id', $v))
            ->when($request->brand_id, fn ($q, $v) => $q->where('brand_id', $v))
            ->when(
                $request->filled('status'),
                fn ($q) => $q->where('status', $request->boolean('status'))
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/product/index', [
            'products' => $products,
            'filters' => $request->only([
                'search',
                'category_id',
                'brand_id',
                'status',
            ]),
            'categories' => Category::select('id', 'name')->get(),
            'brands' => Brand::select('id', 'name')->get(),
        ]);
    }

    public function toggleStatus(Product $product): \Illuminate\Http\RedirectResponse
    {
        $product->update([
            'status' => ! $product->status,
        ]);

        return redirect()->back();
    }

    public function updateField(UpdateProductFieldRequest $request, Product $product): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();

        $product->update([
            $validated['field'] => $validated['value'],
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
        $this->productUpsertService->create(
            $request->validated(),
            new ProductWriteOptions,
        );

        return redirect()->route('admin.product.index')->with('success', 'Продукт добавлен');
    }

    public function edit(Product $product): \Inertia\Response
    {
        return Inertia::render('admin/product/edit', [
            'product' => $product,
            'categories' => Category::select('id', 'name')->get(),
            'subCategories' => SubCategory::select('id', 'name', 'category_id')->get(),
            'childCategories' => ChildCategory::select('id', 'name', 'sub_category_id')->get(),
            'brands' => Brand::select('id', 'name')->where('status', true)->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): \Illuminate\Http\RedirectResponse
    {
        $this->productUpsertService->update(
            $product,
            $request->validated(),
            new ProductWriteOptions,
        );

        return redirect()->route('admin.product.index')->with('success', 'Продукт обновлён');
    }
}
