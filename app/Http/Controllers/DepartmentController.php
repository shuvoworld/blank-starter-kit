<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
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

    protected function authorizeAction(string $ability, ?Model $record = null): void
    {
        if ($record) {
            $this->authorize($ability, $record);
        } else {
            $this->authorize($ability, Department::class);
        }
    }

    public function show(int|string $record): View
    {
        $department = $this->findRecord($record);
        $this->authorize('view', $department);

        return view('departments.show', compact('department'));
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Department::class);

        Department::create($request->validated());

        return $this->successRedirect('created');
    }

    public function update(UpdateDepartmentRequest $request, int|string $record): RedirectResponse
    {
        $department = $this->findRecord($record);
        $this->authorize('update', $department);

        $department->update($request->validated());

        return $this->successRedirect('updated');
    }
}
