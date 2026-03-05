<?php

namespace App\Policies;

use App\Models\EmployeeCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeeCategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Superusers bypass all policy checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Superuser')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view employee categories');
    }

    public function view(User $user, EmployeeCategory $employeeCategory): bool
    {
        return $user->can('view employee categories');
    }

    public function create(User $user): bool
    {
        return $user->can('create employee categories');
    }

    public function update(User $user, EmployeeCategory $employeeCategory): bool
    {
        return $user->can('update employee categories');
    }

    public function delete(User $user, EmployeeCategory $employeeCategory): bool
    {
        return $user->can('delete employee categories');
    }
}
