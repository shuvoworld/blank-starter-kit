<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
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

    public function show(int|string $record): View
    {
        $department = $this->findRecord($record);
        $this->authorizeAction('view', $department);

        return view('departments.show', compact('department'));
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $this->authorizeAction('create');

        Department::create($request->validated());

        return $this->successRedirect('created');
    }

    public function update(UpdateDepartmentRequest $request, int|string $record): RedirectResponse
    {
        $department = $this->findRecord($record);
        $this->authorizeAction('update', $department);

        $department->update($request->validated());

        return $this->successRedirect('updated');
    }
}
