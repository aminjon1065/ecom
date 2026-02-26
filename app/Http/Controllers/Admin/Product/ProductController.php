<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
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
            ->when($request->category_id, fn ($q, $v) => $q->where('category_id', $v)
            )
            ->when($request->brand_id, fn ($q, $v) => $q->where('brand_id', $v)
            )
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

    /**
     * Inline update a single field on a product.
     */
    public function updateField(Request $request, Product $product): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'field' => ['required', 'string', 'in:price,qty,sku'],
            'value' => ['required'],
        ]);

        $field = $validated['field'];
        $value = $validated['value'];

        $rules = match ($field) {
            'price' => ['value' => ['required', 'numeric', 'min:0']],
            'qty' => ['value' => ['required', 'integer', 'min:0']],
            'sku' => ['value' => ['nullable', 'string', 'max:100']],
        };

        $request->validate($rules);

        $product->update([$field => $value]);

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
        $data['slug'] = Str::slug($data['name']);

        // GALLERY
        if ($request->hasFile('gallery')) {
            $data['gallery'] = collect($request->file('gallery'))
                ->map(fn ($file) => $file->store('products/gallery', 'public'))
                ->toArray();
        }

        Product::create($data);

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
        $data = $request->validated();

        // THUMB — only replace if a new file was uploaded
        if ($request->hasFile('thumb_image')) {
            if ($product->thumb_image) {
                Storage::disk('public')->delete($product->thumb_image);
            }
            $data['thumb_image'] = $request->file('thumb_image')
                ->store('products/thumbs', 'public');
        } else {
            unset($data['thumb_image']);
        }

        // Update slug when name changes
        $data['slug'] = Str::slug($data['name']);

        // GALLERY
        if ($request->hasFile('gallery')) {
            $data['gallery'] = collect($request->file('gallery'))
                ->map(fn ($file) => $file->store('products/gallery', 'public'))
                ->toArray();
        }

        $product->update($data);

        return redirect()->route('admin.product.index')->with('success', 'Продукт обновлён');
    }
}
