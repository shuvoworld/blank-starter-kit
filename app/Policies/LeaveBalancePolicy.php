<?php

namespace App\Policies;

use App\Models\LeaveBalance;
use App\Models\User;

class LeaveBalancePolicy
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
     * Can the user see the full list of leave balances?
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view any leave balances');
    }

    /**
     * Can the user view a single leave balance record?
     */
    public function view(User $user, LeaveBalance $leaveBalance): bool
    {
        // Users can view their own balances
        if ($user->id === $leaveBalance->user_id) {
            return true;
        }

        return $user->can('view any leave balances');
    }

    /**
     * Can the user create a new leave balance?
     */
    public function create(User $user): bool
    {
        return $user->can('create leave balances');
    }

    /**
     * Can the user update this leave balance?
     */
    public function update(User $user, LeaveBalance $leaveBalance): bool
    {
        return $user->can('update leave balances');
    }

    /**
     * Can the user delete this leave balance record?
     */
    public function delete(User $user, LeaveBalance $leaveBalance): bool
    {
        return $user->can('delete leave balances');
    }
}
