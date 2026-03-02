<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\StoreLeaveTypeRequest;
use App\Http\Requests\UpdateLeaveTypeRequest;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeaveTypeController extends BaseController
{
    public function __construct(LeaveTypeDataTableController $dataTableController)
    {
        $this->model = LeaveType::class;
        $this->routePrefix = 'leave-types';
        $this->viewPrefix = 'leave-types';
        $this->resourceName = 'Leave type';
        $this->dataTableController = $dataTableController;
    }

    public function show(int|string $record): View
    {
        return view('leave-types.show', ['leaveType' => $this->findRecord($record)]);
    }

    public function store(StoreLeaveTypeRequest $request): RedirectResponse
    {
        LeaveType::create($request->validated());

        return $this->successRedirect('created');
    }

    public function update(UpdateLeaveTypeRequest $request, int|string $record): RedirectResponse
    {
        $this->findRecord($record)->update($request->validated());

        return $this->successRedirect('updated');
    }
}
