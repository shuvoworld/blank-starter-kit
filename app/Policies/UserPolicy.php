<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

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
     * Can the user see the full list of users?
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view users');
    }

    /**
     * Can the user view a specific user record?
     */
    public function view(User $user, User $model): bool
    {
        // Users can view their own profile
        if ($user->id === $model->id) {
            return true;
        }

        return $user->can('view users');
    }

    /**
     * Can the user open the create form and submit a new user?
     */
    public function create(User $user): bool
    {
        return $user->can('create users');
    }

    /**
     * Can the user open the edit form and save changes to this user?
     */
    public function update(User $user, User $model): bool
    {
        // Users can edit their own profile (except roles/permissions)
        if ($user->id === $model->id) {
            return true;
        }

        return $user->can('update users');
    }

    /**
     * Can the user delete this user record?
     */
    public function delete(User $user, User $model): bool
    {
        // Users cannot delete their own account
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('delete users');
    }

    /**
     * Can the user manage roles for this user?
     */
    public function manageRoles(User $user, User $model): bool
    {
        // Users cannot change their own roles
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('manage user roles');
    }
}
