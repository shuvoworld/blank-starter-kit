<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
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
     * Can the user see the full list of employees?
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view any employees');
    }

    /**
     * Can the user view a single employee record?
     *
     * This is a row-level check: a user can view an employee if they have
     * the general "view any employees" permission, OR if they are viewing
     * their own employee profile (linked via user_id).
     */
    public function view(User $user, Employee $employee): bool
    {
        // General permission — managers and admins
        if ($user->can('view any employees')) {
            return true;
        }

        // Row-level permission — an employee viewing their own record
        if ($employee->user_id !== null && $employee->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Can the user open the create form and submit a new employee?
     */
    public function create(User $user): bool
    {
        return $user->can('create employees');
    }

    /**
     * Can the user open the edit form and save changes to this employee?
     *
     * Row-level check: an employee may also edit their own profile.
     */
    public function update(User $user, Employee $employee): bool
    {
        // General permission — HR and admins
        if ($user->can('update employees')) {
            return true;
        }

        // Row-level permission — an employee updating their own record
        if ($employee->user_id !== null && $employee->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Can the user delete this employee record?
     *
     * Deletion is NOT allowed for the employee's own record — only admins can delete.
     */
    public function delete(User $user, Employee $employee): bool
    {
        return $user->can('delete employees');
    }
}
