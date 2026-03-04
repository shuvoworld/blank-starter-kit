<?php

namespace App\Observers;

use App\Models\LeaveBalance;

class LeaveBalanceObserver
{
    /**
     * Handle the LeaveBalance "created" event.
     *
     * This fires after a new balance row has been saved to the database.
     * Add any side effects that must happen right after creation.
     */
    public function created(LeaveBalance $leaveBalance): void
    {
        // Example: clear cached balance data.
        // cache()->forget('user_balance_'.$leaveBalance->user_id);
    }

    /**
     * Handle the LeaveBalance "updated" event.
     *
     * This fires after a balance row has been changed and saved.
     * Add any side effects that must happen when balance details change.
     */
    public function updated(LeaveBalance $leaveBalance): void
    {
        // Clear cached balance data when balances are updated.
        // cache()->forget('user_balance_'.$leaveBalance->user_id);
    }

    /**
     * Handle the LeaveBalance "deleting" event.
     *
     * This fires BEFORE the balance is removed from the database.
     * Add any cascading effects here.
     */
    public function deleting(LeaveBalance $leaveBalance): void
    {
        // Add cascade logic here if deleting this balance should affect related records.
    }

    /**
     * Handle the LeaveBalance "deleted" event.
     *
     * This fires AFTER the balance has been removed from the database.
     * Add any final cleanup actions here.
     */
    public function deleted(LeaveBalance $leaveBalance): void
    {
        // Clear any caches that held this balance's data.
        // cache()->forget('user_balance_'.$leaveBalance->user_id);
    }
}
