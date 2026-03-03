<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseDataTableController;
use App\Models\LeaveType;

class LeaveTypeDataTableController extends BaseDataTableController
{
    public function __construct()
    {
        $this->model = LeaveType::class;
        $this->routePrefix = 'leave-types';
        $this->rawColumns = ['code_badge', 'is_paid_badge', 'carry_forward_badge', 'status_badge'];

    }

    protected function indexQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return LeaveType::query()->orderBy('sort_order');
    }

    protected function dataTableColumns(): array
    {
        return [
            'code_badge' => fn (LeaveType $leaveType) => '<span class="badge bg-info">'.$leaveType->code.'</span>',

            'is_paid_badge' => fn (LeaveType $leaveType) => '<span class="badge '.($leaveType->is_paid ? 'bg-success' : 'bg-warning').'">'
                .($leaveType->is_paid ? 'Paid' : 'Unpaid').'</span>',

            'max_days_per_year' => fn (LeaveType $leaveType) => $leaveType->max_days_per_year ?? '—',

            'carry_forward_badge' => fn (LeaveType $leaveType) => $leaveType->carry_forward
                ? '<span class="badge bg-success">Yes'.($leaveType->carry_forward_limit ? " (max {$leaveType->carry_forward_limit})" : '').'</span>'
                : '<span class="badge bg-secondary">No</span>',

            'status_badge' => fn (LeaveType $leaveType) => '<span class="badge '.($leaveType->is_active ? 'bg-success' : 'bg-secondary').'">'
                .($leaveType->is_active ? 'Active' : 'Inactive').'</span>',
        ];
    }

    /**
     * LeaveType has a View button in addition to Edit and Delete.
     */
    protected function actionColumn($leaveType): string
    {
        $showUrl = route('leave-types.show', $leaveType);
        $editUrl = route('leave-types.edit', $leaveType);
        $deleteUrl = route('leave-types.destroy', $leaveType);

        return '
            <div class="btn-group btn-group-sm">
                <a href="'.$showUrl.'" class="btn btn-info" title="View">
                    <i class="bi bi-eye"></i>
                </a>
                <a href="'.$editUrl.'" class="btn btn-primary" title="Edit">
                    <i class="bi bi-pencil"></i>
                </a>
                <button type="button" class="btn btn-danger btn-delete"
                    data-url="'.$deleteUrl.'"
                    data-name="'.e($leaveType->name).'" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        ';
    }

    public function tableColumns(): array
    {
        return [
            [
                'data' => 'DT_RowIndex',
                'name' => 'DT_RowIndex',
                'label' => '#',
                'width' => '50',
                'orderable' => false,
                'searchable' => false,
                'className' => 'text-center',
            ],

            [
                'data' => 'name',
                'name' => 'name',
                'label' => 'Name',
            ],
            [
                'data' => 'code_badge',
                'name' => 'code',
                'label' => 'Code',
                'orderable' => true,
                'searchable' => false,
                'className' => 'text-center',
            ],
            [
                'data' => 'is_paid_badge',
                'name' => 'is_paid',
                'label' => 'Paid',
                'orderable' => false,
                'searchable' => false,
                'className' => 'text-center',
            ],
            [
                'data' => 'status_badge',
                'name' => 'is_active',
                'label' => 'Status',
                'orderable' => false,
                'searchable' => false,
                'className' => 'text-center',
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
