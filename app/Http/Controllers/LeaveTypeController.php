<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\LeaveTypeRequest;
use App\Models\LeaveType;
use App\Services\LeaveTypeService;
use Illuminate\View\View;

class LeaveTypeController extends BaseController
{
    public function __construct(
        LeaveTypeDataTableController $dataTableController,
        LeaveTypeService $service
    ) {
        $this->model               = LeaveType::class;
        $this->routePrefix         = 'leave-types';
        $this->viewPrefix          = 'leave-types';
        $this->resourceName        = 'Leave type';
        $this->dataTableController = $dataTableController;
        $this->service             = $service;
    }

    protected function requestClass(): string
    {
        return LeaveTypeRequest::class;
    }

    protected function fileFields(): array
    {
        return [
            'supporting_document' => 'leave-types/documents',
        ];
    }

    public function show(int|string $record): View
    {
        $leaveType = $this->findRecord($record);
        $this->authorizeAction('view', $leaveType);

        return view('leave-types.show', compact('leaveType'));
    }
}