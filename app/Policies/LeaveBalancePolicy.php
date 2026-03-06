<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LeaveBalancePolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'leave-balances';
    }

    /**
     * Any authenticated user can reach the list.
     * The query is scoped to their own records unless they have leave-balances.view.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Users can view their own balance. */
    public function view(User $user, Model $model): bool
    {
        return $user->id === $model->user_id || $user->can('leave-balances.view');
    }
}
