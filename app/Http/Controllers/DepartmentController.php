<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use Illuminate\View\View;

class DepartmentController extends BaseController
{
    public function __construct(DepartmentDataTableController $dataTableController)
    {
        $this->model = Department::class;
        $this->routePrefix = 'departments';
        $this->viewPrefix = 'departments';
        $this->resourceName = 'Department';
        $this->dataTableController = $dataTableController;
    }

    protected function requestClass(): ?string
    {
        return DepartmentRequest::class;
    }

    public function show(int|string $record): View
    {
        $department = $this->findRecord($record);
        $this->authorizeAction('view', $department);

        return view('departments.show', compact('department'));
    }
}
