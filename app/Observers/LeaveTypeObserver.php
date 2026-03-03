<?php

namespace App\Observers;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;

class LeaveTypeObserver
{
    /**
     * Handle the LeaveType "created" event.
     *
     * Fires after a new leave type row has been saved to the database.
     */
    public function created(LeaveType $leaveType): void
    {
        //
    }

    /**
     * Handle the LeaveType "updated" event.
     *
     * Fires after a leave type row has been changed and saved.
     */
    public function updated(LeaveType $leaveType): void
    {
        //
    }

    /**
     * Handle the LeaveType "deleting" event.
     *
     * Fires BEFORE the leave type is removed from the database.
     * This is the correct place for cascade effects — the record still
     * exists and can be referenced while we clean up dependents.
     *
     * Cascade 1 — Cancel pending leave requests of this type.
     * We cancel rather than delete so the request history is preserved
     * in the audit trail. We loop individually (not a mass update) so
     * HasActivityLog records each cancellation with a timestamp.
     *
     * Cascade 2 — Delete leave balances for this type.
     * Balances are meaningless once the leave type no longer exists,
     * so they are removed entirely.
     */
    public function deleting(LeaveType $leaveType): void
    {
        $pendingRequests = LeaveRequest::query()
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'pending')
            ->get();

        foreach ($pendingRequests as $leaveRequest) {
            $leaveRequest->status = 'cancelled';
            $leaveRequest->save();
        }

        LeaveBalance::query()
            ->where('leave_type_id', $leaveType->id)
            ->delete();
    }

    /**
     * Handle the LeaveType "deleted" event.
     *
     * Fires AFTER the leave type has been removed from the database.
     */
    public function deleted(LeaveType $leaveType): void
    {
        //
    }
}
