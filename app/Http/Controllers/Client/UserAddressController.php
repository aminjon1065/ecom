<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreUserAddressRequest;
use App\Http\Requests\Client\UpdateUserAddressRequest;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class UserAddressController extends Controller
{
    public function store(StoreUserAddressRequest $request): RedirectResponse
    {
        UserAddress::create([
            'user_id' => Auth::id(),
            ...$request->validated(),
        ]);

        return redirect()->back();
    }

    public function update(UpdateUserAddressRequest $request, UserAddress $address): RedirectResponse
    {
        $address->update($request->validated());

        return redirect()->back();
    }

    public function destroy(UserAddress $address): RedirectResponse
    {
        $this->authorize('delete', $address);

        $address->delete();

        return redirect()->back();
    }
}
