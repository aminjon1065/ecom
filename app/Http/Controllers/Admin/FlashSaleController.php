<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFlashSaleRequest;
use App\Http\Requests\Admin\UpdateFlashSaleRequest;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FlashSaleController extends Controller
{
    public function index(): Response
    {
        $flashSales = FlashSale::with('product:id,name,thumb_image,price')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $products = Product::where('status', true)
            ->where('is_approved', true)
            ->select(['id', 'name'])
            ->get();

        return Inertia::render('admin/flash-sale/index', [
            'flashSales' => $flashSales,
            'products' => $products,
        ]);
    }

    public function store(StoreFlashSaleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['status'] = $data['status'] ?? true;
        $data['show_at_main'] = $data['show_at_main'] ?? true;

        FlashSale::create($data);

        return redirect()->back();
    }

    public function update(UpdateFlashSaleRequest $request, FlashSale $flashSale): RedirectResponse
    {
        $data = $request->validated();

        $flashSale->update($data);

        return redirect()->back();
    }

    public function toggleStatus(FlashSale $flashSale): RedirectResponse
    {
        $flashSale->update(['status' => ! $flashSale->status]);

        return redirect()->back();
    }

    public function destroy(FlashSale $flashSale): RedirectResponse
    {
        $flashSale->delete();

        return redirect()->back();
    }
}
