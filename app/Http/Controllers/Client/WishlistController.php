<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return Inertia::render('client/wishlist', [
            'wishlists' => $wishlists,
        ]);
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
}
