<?php

namespace App\Policies;

use App\Models\Holiday;
use App\Models\User;

class HolidayPolicy
{
    /**
     * Super admins skip all checks below and are automatically allowed to do everything.
     *
     * Returning true  = allow immediately, do not run the specific method.
     * Returning null  = continue to the specific method below.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Superuser')) {
            return true;
        }

        return null;
    }

    /**
     * Can the user see the full list of holidays?
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view any holidays');
    }

    /**
     * Can the user view a single holiday record?
     */
    public function view(User $user, Holiday $holiday): bool
    {
        return $user->can('view any holidays');
    }

    /**
     * Can the user open the create form and submit a new holiday?
     */
    public function create(User $user): bool
    {
        return $user->can('create holidays');
    }

    /**
     * Can the user open the edit form and save changes to this holiday?
     */
    public function update(User $user, Holiday $holiday): bool
    {
        return $user->can('update holidays');
    }

    /**
     * Can the user delete this holiday record?
     */
    public function delete(User $user, Holiday $holiday): bool
    {
        return $user->can('delete holidays');
    }
}
