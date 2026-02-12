<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellerProductController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::whereNotNull('vendor_id')
            ->with(['vendor.user:id,name', 'category:id,name'])
            ->select(['id', 'name', 'slug', 'thumb_image', 'price', 'offer_price', 'qty', 'code', 'status', 'is_approved', 'vendor_id', 'category_id', 'created_at']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('approval')) {
            $query->where('is_approved', $request->approval);
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('admin/seller-product/index', [
            'products' => $products,
            'filters' => $request->only(['search', 'approval']),
        ]);
    }

    public function toggleApproval(Product $product): RedirectResponse
    {
        $product->update(['is_approved' => !$product->is_approved]);

        return redirect()->back();
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        $product->update(['status' => !$product->status]);

        return redirect()->back();
    }
}
