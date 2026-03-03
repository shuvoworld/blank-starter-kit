<?php

namespace App\Policies;

use App\Models\LeaveType;
use App\Models\User;

class LeaveTypePolicy
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
     * Can the user see the full list of leave types?
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view any leave types');
    }

    /**
     * Can the user view a single leave type record?
     */
    public function view(User $user, LeaveType $leaveType): bool
    {
        return $user->can('view any leave types');
    }

    /**
     * Can the user open the create form and submit a new leave type?
     */
    public function create(User $user): bool
    {
        return $user->can('create leave types');
    }

    /**
     * Can the user open the edit form and save changes to this leave type?
     */
    public function update(User $user, LeaveType $leaveType): bool
    {
        return $user->can('update leave types');
    }

    /**
     * Can the user delete this leave type record?
     */
    public function delete(User $user, LeaveType $leaveType): bool
    {
        return $user->can('delete leave types');
    }
}
