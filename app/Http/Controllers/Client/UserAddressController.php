<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAddressController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'address' => 'required|string|max:500',
            'description' => 'nullable|string|max:255',
        ]);

        UserAddress::create([
            'user_id' => Auth::id(),
            ...$validated,
        ]);

        return redirect()->back();
    }

    public function update(Request $request, UserAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'address' => 'required|string|max:500',
            'description' => 'nullable|string|max:255',
        ]);

        $address->update($validated);

        return redirect()->back();
    }

    public function destroy(UserAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === Auth::id(), 403);

        $address->delete();

        return redirect()->back();
    }
}
