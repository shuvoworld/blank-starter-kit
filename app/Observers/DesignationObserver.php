<?php

namespace App\Observers;

use App\Models\Designation;
use App\Models\Employee;

class DesignationObserver
{
    /**
     * Handle the Designation "created" event.
     *
     * Fires after a new designation has been saved to the database.
     */
    public function created(Designation $designation): void
    {
        // Example: clear a cached designation list.
        // cache()->forget('active_designations');
    }

    /**
     * Handle the Designation "updated" event.
     *
     * Fires after a designation record has been changed and saved.
     */
    public function updated(Designation $designation): void
    {
        // Example: if the designation name changed, sync to related employee records.
        // if ($designation->wasChanged('name')) { ... }
    }

    /**
     * Handle the Designation "deleting" event.
     *
     * Fires BEFORE the designation is removed from the database.
     *
     * Cascade: When a designation is deleted, we unlink all employees that
     * hold it. We set their designation_id to null rather than deleting
     * the employees — a job title change should not remove people.
     *
     * We loop individually so HasActivityLog records each employee change.
     */
    public function deleting(Designation $designation): void
    {
        $employeesWithDesignation = Employee::query()
            ->where('designation_id', $designation->id)
            ->get();

        foreach ($employeesWithDesignation as $employee) {
            $employee->designation_id = null;
            $employee->save();
        }
    }

    /**
     * Handle the Designation "deleted" event.
     *
     * Fires AFTER the designation has been removed from the database.
     */
    public function deleted(Designation $designation): void
    {
        // Example: clear any designation-related caches.
        // cache()->forget('active_designations');
    }
}
