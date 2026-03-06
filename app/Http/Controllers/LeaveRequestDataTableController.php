<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseDataTableController;
use App\Models\LeaveRequest;

class LeaveRequestDataTableController extends BaseDataTableController
{
    public function __construct()
    {
        $this->model = LeaveRequest::class;
        $this->routePrefix = 'leave-requests';
        $this->rawColumns = ['employee_name', 'leave_type', 'date_range', 'total_days', 'status_badge', 'updated_at', 'updated_by'];
    }

    protected function indexQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $request = request();

        $user = auth()->user();

        return LeaveRequest::query()
            ->with(['user', 'leaveType', 'approvedBy', 'rejectedBy', 'updatedBy'])
            ->when(! $user->can('leave-requests.view'), fn ($q) => $q->where('user_id', $user->id))
            ->when($request->input('status') && $request->status !== 'all', fn ($q, $status) => $q->where('status', $status))
            ->when($request->input('year'), fn ($q, $year) => $q->where('year', $year))
            ->orderBy('created_at', 'desc');
    }

    protected function dataTableColumns(): array
    {
        return [
            'employee_name' => fn (LeaveRequest $leaveRequest) => $leaveRequest->user->name,

            'leave_type' => fn (LeaveRequest $leaveRequest) => '<span class="badge bg-info">'
                .$leaveRequest->leaveType->code.'</span> '.$leaveRequest->leaveType->name,

            'date_range' => fn (LeaveRequest $leaveRequest) => $leaveRequest->start_date->format('M d, Y')
                .' - '.$leaveRequest->end_date->format('M d, Y'),

            'total_days' => fn (LeaveRequest $leaveRequest) => $leaveRequest->total_days.' day'
                .($leaveRequest->total_days > 1 ? 's' : ''),

            'status_badge' => fn (LeaveRequest $leaveRequest) => $leaveRequest->status_badge,

            'updated_at' => fn (LeaveRequest $leaveRequest) => $leaveRequest->updated_at->format('M d, Y H:i'),

            'updated_by' => fn (LeaveRequest $leaveRequest) => $leaveRequest->updatedBy?->name ?? '—',
        ];
    }

    protected function actionColumn($leaveRequest): string
    {
        $showUrl = route('leave-requests.show', $leaveRequest);
        $canApprove = auth()->user()?->can('leave-requests.approve');
        $canReject = auth()->user()?->can('leave-requests.reject');

        $actions = '
            <div class="btn-group btn-group-sm">
                <a href="'.$showUrl.'" class="btn btn-info" title="View">
                    <i class="bi bi-eye"></i>
                </a>';

        // Approve button for pending requests
        if ($leaveRequest->isPending() && $canApprove) {
            $actions .= '
                <button type="button" class="btn btn-success btn-approve"
                    data-url="'.route('leave-requests.approve', $leaveRequest).'"
                    data-name="'.e($leaveRequest->user->name).'" title="Approve">
                    <i class="bi bi-check-lg"></i>
                </button>';
        }

        // Reject button for pending requests
        if ($leaveRequest->isPending() && $canReject) {
            $actions .= '
                <button type="button" class="btn btn-danger btn-reject"
                    data-url="'.route('leave-requests.reject', $leaveRequest).'"
                    data-name="'.e($leaveRequest->user->name).'" title="Reject">
                    <i class="bi bi-x-lg"></i>
                </button>';
        }

        // Cancel button for own pending requests
        if ($leaveRequest->canBeCancelled() && $leaveRequest->user_id === auth()->id()) {
            $actions .= '
                <button type="button" class="btn btn-warning btn-cancel"
                    data-url="'.route('leave-requests.cancel', $leaveRequest).'"
                    data-name="'.e($leaveRequest->leaveType->name).'" title="Cancel">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>';
        }

        $actions .= '</div>';

        return $actions;
    }

    public function tableColumns(): array
    {
        return [
            [
                'data' => 'id',
                'name' => 'id',
                'label' => 'ID',
                'width' => '70',
                'className' => 'text-center',
            ],
            [
                'data' => 'employee_name',
                'name' => 'user.name',
                'label' => 'Employee',
            ],
            [
                'data' => 'leave_type',
                'name' => 'leave_type_id',
                'label' => 'Leave Type',
                'orderable' => false,
            ],
            [
                'data' => 'date_range',
                'name' => 'start_date',
                'label' => 'Date Range',
                'orderable' => true,
            ],
            [
                'data' => 'status_badge',
                'name' => 'status',
                'label' => 'Status',
                'orderable' => true,
                'searchable' => false,
                'className' => 'text-center',
            ],
            [
                'data' => 'updated_at',
                'name' => 'updated_at',
                'label' => 'Updated At',
                'className' => 'text-nowrap',
            ],
            [
                'data' => 'updated_by',
                'name' => 'updated_by',
                'label' => 'Updated By',
                'orderable' => false,
                'searchable' => false,
            ],
            [
                'data' => 'action',
                'name' => 'action',
                'label' => 'Actions',
                'width' => '180',
                'orderable' => false,
                'searchable' => false,
                'className' => 'text-center',
            ],
        ];
    }
}
