<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
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
     * Any authenticated admin or vendor may list products.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'vendor']);
    }

    /**
     * Vendors can only view their own products.
     */
    public function view(User $user, Product $product): bool
    {
        return $this->isOwner($user, $product);
    }

    /**
     * Any vendor or admin may initiate product creation.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'vendor']);
    }

    /**
     * Vendors can only update products they own.
     */
    public function update(User $user, Product $product): bool
    {
        return $this->isOwner($user, $product);
    }

    /**
     * Vendors can only delete products they own.
     */
    public function delete(User $user, Product $product): bool
    {
        return $this->isOwner($user, $product);
    }

    /**
     * Vendors can only toggle status on their own products.
     */
    public function toggleStatus(User $user, Product $product): bool
    {
        return $this->isOwner($user, $product);
    }

    // -------------------------------------------------------------------------

    private function isOwner(User $user, Product $product): bool
    {
        return $user->vendor !== null
            && $user->vendor->id === $product->vendor_id;
    }
}
