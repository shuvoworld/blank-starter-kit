<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\LeaveRequest;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     *
     * Fires after a new employee row has been saved to the database.
     *
     * Cascade: Initialize leave balances for the new employee so they
     * can request leave immediately without a manual HR setup step.
     */
    public function created(Employee $employee): void
    {
        // Only employees who have a linked User account can submit leave requests.
        if ($employee->user_id === null) {
            return;
        }

        // Uncomment and adapt once LeaveBalanceService exposes an
        // "initialize for new employee" method:
        //
        // $leaveBalanceService = app(\App\Services\LeaveBalanceService::class);
        // $leaveBalanceService->initializeBalancesForEmployee($employee);
    }

    /**
     * Handle the Employee "updated" event.
     *
     * Fires after an employee record has been changed and saved.
     */
    public function updated(Employee $employee): void
    {
        // Example: if the employee's department changed, recalculate any
        // department-based leave rules or salary grades.
        //
        // if ($employee->wasChanged('department_id')) { ... }
    }

    /**
     * Handle the Employee "deleting" event.
     *
     * Fires BEFORE the employee is removed from the database.
     *
     * Cascade: Cancel all pending leave requests that belong to this
     * employee's user account. We do this before the employee row is
     * deleted so the changes are captured by HasActivityLog and remain
     * visible in the audit trail even after the employee is gone.
     */
    public function deleting(Employee $employee): void
    {
        // Only employees with a linked user account can have leave requests.
        if ($employee->user_id === null) {
            return;
        }

        // Fetch all pending requests for this employee's user account.
        $pendingLeaveRequests = LeaveRequest::query()
            ->where('user_id', $employee->user_id)
            ->where('status', 'pending')
            ->get();

        // Cancel each request individually so the activity log records
        // each cancellation with a timestamp and event context.
        foreach ($pendingLeaveRequests as $leaveRequest) {
            $leaveRequest->status = 'cancelled';
            $leaveRequest->save();
        }
    }

    /**
     * Handle the Employee "deleted" event.
     *
     * Fires AFTER the employee has been removed from the database.
     */
    public function deleted(Employee $employee): void
    {
        // Example: revoke the linked user's system access here.
        // Example: send an offboarding notification to HR.
    }
}
