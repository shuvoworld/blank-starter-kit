<?php

namespace App\Observers;

use App\Models\EmployeeCategory;

class EmployeeCategoryObserver
{
    public function creating(EmployeeCategory $employeeCategory): void
    {
        $employeeCategory->created_by = auth()->id();
        $employeeCategory->updated_by = auth()->id();
    }

    public function updating(EmployeeCategory $employeeCategory): void
    {
        $employeeCategory->updated_by = auth()->id();
    }

    public function deleting(EmployeeCategory $employeeCategory): void
    {
        $employeeCategory->deleted_by = auth()->id();
        $employeeCategory->saveQuietly();
    }
}
