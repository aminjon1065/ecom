<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    public function index(): Response
    {
        $wishlists = Wishlist::where('user_id', Auth::id())
            ->with(['product:id,name,slug,thumb_image,price,offer_price,offer_start_date,offer_end_date,qty,status'])
            ->latest()
            ->get();

        $wishlistSummary = [
            'total' => $wishlists->count(),
            'available' => $wishlists->filter(function (Wishlist $wishlist): bool {
                return $wishlist->product !== null
                    && $wishlist->product->status
                    && $wishlist->product->qty > 0;
            })->count(),
            'out_of_stock' => $wishlists->filter(function (Wishlist $wishlist): bool {
                return $wishlist->product === null
                    || ! $wishlist->product->status
                    || $wishlist->product->qty < 1;
            })->count(),
            'potential_savings' => round($wishlists->sum(function (Wishlist $wishlist): float {
                if ($wishlist->product === null) {
                    return 0.0;
                }

                $basePrice = (float) $wishlist->product->price;
                $effectivePrice = $this->getEffectivePrice($wishlist->product);

                return max(0, $basePrice - $effectivePrice);
            }), 2),
        ];

        return Inertia::render('client/wishlist', [
            'wishlists' => $wishlists,
            'wishlistSummary' => $wishlistSummary,
        ]);
    }

    private function getEffectivePrice(object $product): float
    {
        if (! $product->offer_price || ! $product->offer_start_date || ! $product->offer_end_date) {
            return (float) $product->price;
        }

        $now = now();

        if ($now->between(Carbon::parse($product->offer_start_date), Carbon::parse($product->offer_end_date))) {
            return (float) $product->offer_price;
        }

        return (float) $product->price;
    }

    public function toggle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($wishlist) {
            $wishlist->delete();
        } else {
            Wishlist::create([
                'user_id' => Auth::id(),
                'product_id' => $validated['product_id'],
            ]);
        }

        return redirect()->back();
    }

    public function destroy(Wishlist $wishlist): RedirectResponse
    {
        abort_unless($wishlist->user_id === Auth::id(), 403);

        $wishlist->delete();

        return redirect()->back();
    }

    public function moveAllToCart(): RedirectResponse
    {
        $wishlists = Wishlist::query()
            ->where('user_id', Auth::id())
            ->with('product:id,qty,status,is_approved')
            ->get();

        $moved = 0;

        foreach ($wishlists as $wishlist) {
            if (! $wishlist->product || ! $wishlist->product->status || ! $wishlist->product->is_approved || $wishlist->product->qty < 1) {
                continue;
            }

            $existingCart = Cart::query()
                ->where('user_id', Auth::id())
                ->where('product_id', $wishlist->product_id)
                ->first();

            if ($existingCart) {
                $nextQuantity = min($existingCart->quantity + 1, (int) $wishlist->product->qty, 100);
                if ($nextQuantity > $existingCart->quantity) {
                    $existingCart->update(['quantity' => $nextQuantity]);
                    $moved++;
                }
            } else {
                Cart::query()->create([
                    'user_id' => Auth::id(),
                    'product_id' => $wishlist->product_id,
                    'quantity' => 1,
                ]);
                $moved++;
            }
        }

        if ($moved > 0) {
            Wishlist::query()
                ->where('user_id', Auth::id())
                ->delete();

            return redirect()->route('cart.index')
                ->with('success', "Перенесено в корзину: {$moved}.");
        }

        return redirect()->back()
            ->with('warning', 'Нет доступных товаров для переноса в корзину.');
    }
}
