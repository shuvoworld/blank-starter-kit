<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EmployeePolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'employees';
    }

    /** Employees can view their own record. */
    public function view(User $user, Model $model): bool
    {
        return $user->can('employees.view') || $model->user_id === $user->id;
    }

    /** Employees can edit their own record; only admins can delete. */
    public function update(User $user, Model $model): bool
    {
        return $user->can('employees.update') || $model->user_id === $user->id;
    }
}
