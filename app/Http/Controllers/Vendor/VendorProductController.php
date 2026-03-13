<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreVendorProductRequest;
use App\Http\Requests\Vendor\UpdateVendorProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\SubCategory;
use App\Services\Product\ProductUpsertService;
use App\Services\Product\ProductWriteOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class VendorProductController extends Controller
{
    public function __construct(
        private readonly ProductUpsertService $productUpsertService,
    ) {}

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

    public function store(StoreVendorProductRequest $request): RedirectResponse
    {
        $vendor = Auth::user()->vendor;

        $this->productUpsertService->create(
            $request->validated(),
            new ProductWriteOptions(
                vendorId: $vendor->id,
                forceApproval: false,
                forceStatus: true,
                appendRandomSuffix: true,
            ),
        );

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

    public function update(UpdateVendorProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->productUpsertService->update(
            $product,
            $request->validated(),
            new ProductWriteOptions(
                forceApproval: false,
            ),
        );

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
