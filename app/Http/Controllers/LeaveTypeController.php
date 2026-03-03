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
        $this->model = LeaveType::class;
        $this->routePrefix = 'leave-types';
        $this->viewPrefix = 'leave-types';
        $this->resourceName = 'Leave type';
        $this->dataTableController = $dataTableController;
    }

    /**
     * Delegate authorization to LeaveTypePolicy.
     *
     * Called by BaseController before index/create/edit/destroy.
     * Also called explicitly before store/update below.
     */
    protected function authorizeAction(string $ability, ?Model $record = null): void
    {
        if ($record) {
            $this->authorize($ability, $record);
        } else {
            $this->authorize($ability, LeaveType::class);
        }
    }

    public function show(int|string $record): View
    {
        $leaveType = $this->findRecord($record);
        $this->authorize('view', $leaveType);

        return view('leave-types.show', compact('leaveType'));
    }

    public function store(StoreLeaveTypeRequest $request): RedirectResponse
    {
        $this->authorize('create', LeaveType::class);

        LeaveType::create($request->validated());

        return $this->successRedirect('created');
    }

    public function update(UpdateLeaveTypeRequest $request, int|string $record): RedirectResponse
    {
        $leaveType = $this->findRecord($record);
        $this->authorize('update', $leaveType);

        $leaveType->update($request->validated());

        return $this->successRedirect('updated');
    }
}
