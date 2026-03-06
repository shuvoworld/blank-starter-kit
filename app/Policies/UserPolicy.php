<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserPolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'users';
    }

    /** Users can view their own profile. */
    public function view(User $user, Model $model): bool
    {
        return $user->id === $model->id || $user->can('users.view');
    }

    /** Users can edit their own profile (except roles/permissions). */
    public function update(User $user, Model $model): bool
    {
        return $user->id === $model->id || $user->can('users.update');
    }

    /** Users cannot delete their own account. */
    public function delete(User $user, Model $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('users.delete');
    }

    /** Users cannot change their own roles. */
    public function manageRoles(User $user, Model $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('users.manage-roles');
    }
}
