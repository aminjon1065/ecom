<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class VendorProductController extends Controller
{
    public function index(Request $request): Response
    {
        $vendor = Auth::user()->vendor;

        $query = Product::where('vendor_id', $vendor->id)
            ->with(['category:id,name', 'brand:id,name']);

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('status', $request->boolean('status'));
        }

        if ($request->has('is_approved') && $request->input('is_approved') !== '') {
            $query->where('is_approved', $request->boolean('is_approved'));
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('vendor/product/index', [
            'products' => $products,
            'filters' => $request->only(['search', 'status', 'is_approved']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('vendor/product/create', [
            'categories' => Category::where('status', true)->get(['id', 'name']),
            'subCategories' => SubCategory::where('status', true)->get(['id', 'name', 'category_id']),
            'childCategories' => ChildCategory::where('status', true)->get(['id', 'name', 'sub_category_id']),
            'brands' => Brand::where('status', true)->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $vendor = Auth::user()->vendor;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|integer|unique:products,code',
            'thumb_image' => 'required|image|max:5120',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'child_category_id' => 'nullable|exists:child_categories,id',
            'brand_id' => 'required|exists:brands,id',
            'qty' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0',
            'offer_start_date' => 'nullable|date',
            'offer_end_date' => 'nullable|date|after_or_equal:offer_start_date',
            'short_description' => 'required|string|max:500',
            'long_description' => 'required|string',
            'video_link' => 'nullable|url|max:500',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'product_type' => 'nullable|string|max:50',
        ]);

        $validated['vendor_id'] = $vendor->id;
        $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(5);
        $validated['is_approved'] = false;
        $validated['status'] = true;

        if ($request->hasFile('thumb_image')) {
            $validated['thumb_image'] = $request->file('thumb_image')->store('products', 'public');
        }

        Product::create($validated);

        return redirect()->route('vendor.product.index');
    }

    public function edit(Product $product): Response
    {
        $this->authorize('update', $product);

        return Inertia::render('vendor/product/edit', [
            'product' => $product,
            'categories' => Category::where('status', true)->get(['id', 'name']),
            'subCategories' => SubCategory::where('status', true)->get(['id', 'name', 'category_id']),
            'childCategories' => ChildCategory::where('status', true)->get(['id', 'name', 'sub_category_id']),
            'brands' => Brand::where('status', true)->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|integer|unique:products,code,'.$product->id,
            'thumb_image' => 'nullable|image|max:5120',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'child_category_id' => 'nullable|exists:child_categories,id',
            'brand_id' => 'required|exists:brands,id',
            'qty' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0',
            'offer_start_date' => 'nullable|date',
            'offer_end_date' => 'nullable|date|after_or_equal:offer_start_date',
            'short_description' => 'required|string|max:500',
            'long_description' => 'required|string',
            'video_link' => 'nullable|url|max:500',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'product_type' => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('thumb_image')) {
            if ($product->thumb_image) {
                Storage::disk('public')->delete($product->thumb_image);
            }
            $validated['thumb_image'] = $request->file('thumb_image')->store('products', 'public');
        } else {
            // No new file uploaded — keep the existing image, don't overwrite with null.
            unset($validated['thumb_image']);
        }

        // Any vendor edit resets approval — admin must re-approve before the
        // product becomes visible on the storefront again.
        $validated['is_approved'] = false;

        $product->update($validated);

        return redirect()->route('vendor.product.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        if ($product->thumb_image) {
            Storage::disk('public')->delete($product->thumb_image);
        }

        $product->delete();

        return redirect()->back();
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        $this->authorize('toggleStatus', $product);

        $product->update(['status' => ! $product->status]);

        return redirect()->back();
    }
}
