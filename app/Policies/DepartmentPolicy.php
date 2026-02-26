<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
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
     * Can the user see the full list of departments?
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view any departments');
    }

    /**
     * Can the user view a single department record?
     */
    public function view(User $user, Department $department): bool
    {
        return $user->can('view any departments');
    }

    /**
     * Can the user open the create form and submit a new department?
     */
    public function create(User $user): bool
    {
        return $user->can('create departments');
    }

    /**
     * Can the user open the edit form and save changes to this department?
     */
    public function update(User $user, Department $department): bool
    {
        return $user->can('update departments');
    }

    /**
     * Can the user delete this department record?
     */
    public function delete(User $user, Department $department): bool
    {
        return $user->can('delete departments');
    }
}
