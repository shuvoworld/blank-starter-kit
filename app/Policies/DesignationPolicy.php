<?php

namespace App\Policies;

use App\Models\Designation;
use App\Models\User;

class DesignationPolicy
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
     * Can the user see the full list of designations?
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view any designations');
    }

    /**
     * Can the user view a single designation record?
     */
    public function view(User $user, Designation $designation): bool
    {
        return $user->can('view any designations');
    }

    /**
     * Can the user open the create form and submit a new designation?
     */
    public function create(User $user): bool
    {
        return $user->can('create designations');
    }

    /**
     * Can the user open the edit form and save changes to this designation?
     */
    public function update(User $user, Designation $designation): bool
    {
        return $user->can('update designations');
    }

    /**
     * Can the user delete this designation record?
     */
    public function delete(User $user, Designation $designation): bool
    {
        return $user->can('delete designations');
    }
}
