<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Super admins skip all checks below and are automatically allowed to do everything.
     *
     * Returning true  = allow immediately, do not run the specific method.
     * Returning null  = continue to the specific method below.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return null;
    }

    /**
     * Can the user see the full list of products?
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view any products');
    }

    /**
     * Can the user view a single product record?
     */
    public function view(User $user, Product $product): bool
    {
        return $user->can('view any products');
    }

    /**
     * Can the user open the create form and submit a new product?
     */
    public function create(User $user): bool
    {
        return $user->can('create products');
    }

    /**
     * Can the user open the edit form and save changes to this product?
     */
    public function update(User $user, Product $product): bool
    {
        return $user->can('update products');
    }

    /**
     * Can the user delete this product record?
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->can('delete products');
    }
}
