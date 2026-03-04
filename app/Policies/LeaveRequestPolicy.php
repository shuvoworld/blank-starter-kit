<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
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
     * Can the user see the full list of leave requests?
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view any leave requests');
    }

    /**
     * Can the user view a single leave request record?
     */
    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        // Users can view their own requests
        if ($user->id === $leaveRequest->user_id) {
            return true;
        }

        return $user->can('view any leave requests');
    }

    /**
     * Can the user create a new leave request?
     */
    public function create(User $user): bool
    {
        return $user->can('create leave requests');
    }

    /**
     * Can the user update this leave request?
     */
    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        // Only pending requests can be updated, and only by the owner
        if ($leaveRequest->isPending() && $user->id === $leaveRequest->user_id) {
            return true;
        }

        return $user->can('update leave requests');
    }

    /**
     * Can the user delete this leave request record?
     */
    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->can('delete leave requests');
    }

    /**
     * Can the user approve this leave request?
     */
    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        if (! $leaveRequest->isPending()) {
            return false;
        }

        return $user->can('approve leave requests');
    }

    /**
     * Can the user reject this leave request?
     */
    public function reject(User $user, LeaveRequest $leaveRequest): bool
    {
        if (! $leaveRequest->isPending()) {
            return false;
        }

        return $user->can('reject leave requests');
    }

    /**
     * Can the user cancel this leave request?
     */
    public function cancel(User $user, LeaveRequest $leaveRequest): bool
    {
        // Users can cancel their own pending requests
        if ($leaveRequest->user_id === $user->id && $leaveRequest->canBeCancelled()) {
            return true;
        }

        return $user->can('cancel leave requests');
    }
}
