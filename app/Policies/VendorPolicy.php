<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;

class VendorPolicy
{
    /**
     * Admins pass all checks automatically.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    /**
     * A vendor can only update their own shop profile.
     */
    public function update(User $user, Vendor $vendor): bool
    {
        return $user->vendor?->id === $vendor->id;
    }

    /**
     * A vendor can only delete their own shop.
     */
    public function delete(User $user, Vendor $vendor): bool
    {
        return $user->vendor?->id === $vendor->id;
    }
}
