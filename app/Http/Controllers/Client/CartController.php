<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreCartRequest;
use App\Http\Requests\Client\UpdateCartRequest;
use App\Models\Cart;
use App\Models\ShippingRules;
use App\Services\Product\RecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        private readonly RecommendationService $recommendationService,
    ) {}

    public function index(): Response
    {
        $cartItems = Cart::where('user_id', Auth::id())
            ->with(['product:id,name,slug,thumb_image,price,offer_price,offer_start_date,offer_end_date,qty,status'])
            ->get();

        $recommendedProducts = $this->recommendationService->cartRecommendations(
            productIds: $cartItems->pluck('product_id')->all(),
            limit: 5,
        );

        $subtotal = round($cartItems->sum(function (Cart $cartItem): float {
            $product = $cartItem->product;
            if (! $product) {
                return 0.0;
            }

            return $product->effectivePrice() * $cartItem->quantity;
        }), 2);

        $savings = round($cartItems->sum(function (Cart $cartItem): float {
            $product = $cartItem->product;
            if (! $product) {
                return 0.0;
            }

            return $product->savingsAmount() * $cartItem->quantity;
        }), 2);

        $freeShippingRule = ShippingRules::query()
            ->where('status', true)
            ->where('type', 'min_cost')
            ->whereNotNull('min_cost')
            ->orderBy('min_cost')
            ->first();

        $freeShippingThreshold = $freeShippingRule?->min_cost !== null
            ? (float) $freeShippingRule->min_cost
            : null;

        $remainingToFreeShipping = $freeShippingThreshold !== null
            ? max(0, round($freeShippingThreshold - $subtotal, 2))
            : null;

        return Inertia::render('client/cart', [
            'cartItems' => $cartItems,
            'recommendedProducts' => $recommendedProducts,
            'cartSummary' => [
                'subtotal' => $subtotal,
                'savings' => $savings,
                'free_shipping_threshold' => $freeShippingThreshold,
                'remaining_to_free_shipping' => $remainingToFreeShipping,
            ],
        ]);
    }

    public function store(StoreCartRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $cart = Cart::where('user_id', Auth::id())
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($cart) {
            $cart->update(['quantity' => $cart->quantity + ($validated['quantity'] ?? 1)]);
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'] ?? 1,
            ]);
        }

        return redirect()->back();
    }

    public function update(UpdateCartRequest $request, Cart $cart): RedirectResponse
    {
        $cart->update($request->validated());

        return redirect()->back();
    }

    public function destroy(Cart $cart): RedirectResponse
    {
        $this->authorize('delete', $cart);

        $cart->delete();

        return redirect()->back();
    }

    public function clear(): RedirectResponse
    {
        Cart::where('user_id', Auth::id())->delete();

        return redirect()->back();
    }
}
