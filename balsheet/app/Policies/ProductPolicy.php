<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Any authenticated user can view the product list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user can view a product.
     */
    public function view(User $user, Product $product): bool
    {
        return true;
    }

    /**
     * Any authenticated user can create products.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Staff and admins can edit products.
     */
    public function update(User $user, Product $product): bool
    {
        return true;
    }

    /**
     * Only administrators can delete products.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only administrators can bulk-delete products.
     */
    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only administrators can restore soft-deleted products.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only administrators can permanently delete products.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }
}
