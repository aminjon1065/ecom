<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(): Response
    {
        $cartItems = Cart::where('user_id', Auth::id())
            ->with(['product:id,name,slug,thumb_image,price,offer_price,offer_start_date,offer_end_date,qty,status'])
            ->get();

        return Inertia::render('client/cart', [
            'cartItems' => $cartItems,
        ]);
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
