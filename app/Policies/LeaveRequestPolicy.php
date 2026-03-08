<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LeaveRequestPolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'leave-requests';
    }

    /**
     * Any authenticated user can reach the list.
     * The query is scoped to their own records unless they have leave-requests.view.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Users can view their own requests. */
    public function view(User $user, Model $model): bool
    {
        return $user->id === $model->user_id || $user->can('leave-requests.view');
    }

    /** Only the owner can update a pending request; admins always can. */
    public function update(User $user, Model $model): bool
    {
        return ($model->isPending() && $user->id === $model->user_id)
            || $user->can('leave-requests.update');
    }

    public function approve(User $user, Model $model): bool
    {
        return $model->isPending() && $user->can('leave-requests.approve');
    }

    public function reject(User $user, Model $model): bool
    {
        return $model->isPending() && $user->can('leave-requests.reject');
    }

    /** The owner can cancel their own cancellable request; admins always can. */
    public function cancel(User $user, Model $model): bool
    {
        return ($model->user_id === $user->id && $model->canBeCancelled())
            || $user->can('leave-requests.cancel');
    }
}
