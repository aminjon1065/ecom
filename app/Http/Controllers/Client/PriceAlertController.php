<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StorePriceAlertRequest;
use App\Models\PriceAlert;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PriceAlertController extends Controller
{
    public function store(StorePriceAlertRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->status && $product->is_approved, 404);

        PriceAlert::query()->updateOrCreate(
            [
                'user_id' => Auth::id(),
                'product_id' => $product->id,
            ],
            [
                'target_price' => $this->effectivePrice($product),
                'is_active' => true,
            ],
        );

        return redirect()->back()->with('success', 'Уведомление о снижении цены включено.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        PriceAlert::query()
            ->where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->delete();

        return redirect()->back()->with('success', 'Уведомление о снижении цены отключено.');
    }

    private function effectivePrice(Product $product): float
    {
        if (
            $product->offer_price &&
            $product->offer_start_date &&
            $product->offer_end_date &&
            now()->between($product->offer_start_date, $product->offer_end_date)
        ) {
            return (float) $product->offer_price;
        }

        return (float) $product->price;
    }
}
