<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\StoreEmployeeCategoryRequest;
use App\Http\Requests\UpdateEmployeeCategoryRequest;
use App\Models\EmployeeCategory;
use Illuminate\Http\RedirectResponse;
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

    public function show(int|string $record): View
    {
        $employeeCategory = $this->findRecord($record);
        $this->authorizeAction('view', $employeeCategory);

        return view('employee-categories.show', compact('employeeCategory'));
    }

    public function store(StoreEmployeeCategoryRequest $request): RedirectResponse
    {
        $this->authorizeAction('create');

        EmployeeCategory::create($request->validated());

        return $this->successRedirect('created');
    }

    public function update(UpdateEmployeeCategoryRequest $request, int|string $record): RedirectResponse
    {
        $employeeCategory = $this->findRecord($record);
        $this->authorizeAction('update', $employeeCategory);

        $employeeCategory->update($request->validated());

        return $this->successRedirect('updated');
    }
}
