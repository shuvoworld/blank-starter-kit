<?php

namespace App\Observers;

use App\Models\Department;
use App\Models\Employee;

class DepartmentObserver
{
    /**
     * Handle the Department "created" event.
     *
     * This fires after a new department row has been saved to the database.
     * Add any side effects that must happen right after creation.
     */
    public function created(Department $department): void
    {
        // Example: clear a cached department list so the UI shows the new entry.
        // cache()->forget('active_departments');
    }

    /**
     * Handle the Department "updated" event.
     *
     * This fires after a department row has been changed and saved.
     * Add any side effects that must happen when department details change.
     */
    public function updated(Department $department): void
    {
        // Example: if the department name changed, you could sync it to related records.
        // if ($department->wasChanged('name')) { ... }
    }

    /**
     * Handle the Department "deleting" event.
     *
     * This fires BEFORE the department is removed from the database.
     * This is the correct place to handle cascading effects because the
     * department record still exists and can be referenced while we clean up.
     *
     * Cascade: When a department is deleted, we unlink all employees that
     * belong to it. We set their department_id to null rather than deleting
     * the employees — losing a department should not delete its staff.
     *
     * We loop through each employee individually (instead of a mass update)
     * so that the HasActivityLog trait records who was unlinked and when.
     */
    public function deleting(Department $department): void
    {
        $employeesInDepartment = Employee::query()
            ->where('department_id', $department->id)
            ->get();

        foreach ($employeesInDepartment as $employee) {
            $employee->department_id = null;
            $employee->save();
        }
    }

    /**
     * Handle the Department "deleted" event.
     *
     * This fires AFTER the department has been removed from the database.
     * Add any final cleanup actions here.
     */
    public function deleted(Department $department): void
    {
        // Example: clear any caches that held this department's data.
        // cache()->forget('active_departments');
    }
}
