<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\EmployeeCategoryRequest;
use App\Models\EmployeeCategory;
use Illuminate\View\View;

class EmployeeCategoryController extends BaseController
{
    public function __construct(EmployeeCategoryDataTableController $dataTableController)
    {
        $this->model = EmployeeCategory::class;
        $this->routePrefix = 'employee-categories';
        $this->viewPrefix = 'employee-categories';
        $this->resourceName = 'Employee Category';
        $this->dataTableController = $dataTableController;
    }

    protected function requestClass(): ?string
    {
        return EmployeeCategoryRequest::class;
    }

    public function show(int|string $record): View
    {
        $employeeCategory = $this->findRecord($record);
        $this->authorizeAction('view', $employeeCategory);

        return view('employee-categories.show', compact('employeeCategory'));
    }
}
