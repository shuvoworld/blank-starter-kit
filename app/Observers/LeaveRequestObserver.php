<?php

namespace App\Observers;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;

class LeaveRequestObserver
{
    /**
     * Handle the LeaveRequest "created" event.
     *
     * This fires after a new request row has been saved to the database.
     * Add any side effects that must happen right after creation.
     */
    public function created(LeaveRequest $leaveRequest): void
    {
        // Update pending days in balance when a request is created
        $balance = LeaveBalance::query()
            ->where('user_id', $leaveRequest->user_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('year', $leaveRequest->year)
            ->first();

        if ($balance) {
            $balance->updatePendingDays();
        }
    }

    /**
     * Handle the LeaveRequest "updated" event.
     *
     * This fires after a request row has been changed and saved.
     * Add any side effects that must happen when request details change.
     */
    public function updated(LeaveRequest $leaveRequest): void
    {
        // Update pending days when status changes
        if ($leaveRequest->wasChanged('status')) {
            $balance = LeaveBalance::query()
                ->where('user_id', $leaveRequest->user_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', $leaveRequest->year)
                ->first();

            if ($balance) {
                $balance->updatePendingDays();
            }
        }
    }

    /**
     * Handle the LeaveRequest "deleting" event.
     *
     * This fires BEFORE the request is removed from the database.
     * Add any cascading effects here.
     */
    public function deleting(LeaveRequest $leaveRequest): void
    {
        // Update pending days when a request is deleted
        $balance = LeaveBalance::query()
            ->where('user_id', $leaveRequest->user_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('year', $leaveRequest->year)
            ->first();

        if ($balance) {
            $balance->updatePendingDays();
        }
    }

    /**
     * Handle the LeaveRequest "deleted" event.
     *
     * This fires AFTER the request has been removed from the database.
     * Add any final cleanup actions here.
     */
    public function deleted(LeaveRequest $leaveRequest): void
    {
        // Add any final cleanup here
    }
}
