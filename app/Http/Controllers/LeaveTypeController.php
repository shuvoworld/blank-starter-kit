<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\StoreLeaveTypeRequest;
use App\Http\Requests\UpdateLeaveTypeRequest;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeaveTypeController extends BaseController
{
    public function __construct(LeaveTypeDataTableController $dataTableController)
    {
        $this->routePrefix        = 'leave-types';
        $this->viewPrefix         = 'leave-types';
        $this->resourceName       = 'Leave type';
        $this->dataTableController = $dataTableController;
    }

    /**
     * LeaveType has a dedicated show view rather than redirecting to edit.
     * Return type narrowed to View — valid since View is within base View|RedirectResponse.
     * Parameter kept as Model — PHP forbids narrowing parameter types in child classes.
     */
    public function show(Model $record): View
    {
        return view('leave-types.show', ['leaveType' => $record]);
    }

    public function store(StoreLeaveTypeRequest $request): RedirectResponse
    {
        LeaveType::create($request->validated());

        return $this->successRedirect('created');
    }

    public function update(UpdateLeaveTypeRequest $request, Model $record): RedirectResponse
    {
        $record->update($request->validated());

        return $this->successRedirect('updated');
    }
}
