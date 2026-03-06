<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class BasePolicy
{
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

    /**
     * The Spatie permission resource prefix, e.g. 'users', 'leave-types'.
     */
    abstract protected function resource(): string;

    public function viewAny(User $user): bool
    {
        return $user->can("{$this->resource()}.view");
    }

    public function view(User $user, Model $model): bool
    {
        return $user->can("{$this->resource()}.view");
    }

    public function create(User $user): bool
    {
        return $user->can("{$this->resource()}.create");
    }

    public function update(User $user, Model $model): bool
    {
        return $user->can("{$this->resource()}.update");
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->can("{$this->resource()}.delete");
    }
}
