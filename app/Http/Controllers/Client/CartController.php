<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\ShippingRules;
use App\Services\Product\RecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

            return $this->getEffectivePrice($product) * $cartItem->quantity;
        }), 2);

        $savings = round($cartItems->sum(function (Cart $cartItem): float {
            $product = $cartItem->product;
            if (! $product) {
                return 0.0;
            }

            $basePrice = (float) $product->price;
            $effectivePrice = $this->getEffectivePrice($product);

            return max(0, ($basePrice - $effectivePrice) * $cartItem->quantity);
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

    private function getEffectivePrice(object $product): float
    {
        if (! $product->offer_price || ! $product->offer_start_date || ! $product->offer_end_date) {
            return (float) $product->price;
        }

        if (now()->between($product->offer_start_date, $product->offer_end_date)) {
            return (float) $product->offer_price;
        }

        return (float) $product->price;
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:100',
        ]);

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

    public function update(Request $request, Cart $cart): RedirectResponse
    {
        abort_unless($cart->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $cart->update($validated);

        return redirect()->back();
    }

    public function destroy(Cart $cart): RedirectResponse
    {
        abort_unless($cart->user_id === Auth::id(), 403);

        $cart->delete();

        return redirect()->back();
    }

    public function clear(): RedirectResponse
    {
        Cart::where('user_id', Auth::id())->delete();

        return redirect()->back();
    }
}
