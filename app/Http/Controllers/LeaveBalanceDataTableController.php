<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseDataTableController;
use App\Models\LeaveBalance;

class LeaveBalanceDataTableController extends BaseDataTableController
{
    public function __construct()
    {
        $this->model = LeaveBalance::class;
        $this->routePrefix = 'leave-balances';
        $this->rawColumns = ['leave_type', 'entitlement', 'usage', 'pending', 'remaining', 'updated_at', 'updated_by'];
    }

    protected function indexQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $request = request();

        return LeaveBalance::query()
            ->with(['user', 'leaveType', 'updatedBy'])
            ->when($request->input('year'), fn ($q, $year) => $q->where('year', $year))
            ->when($request->input('leave_type_id'), fn ($q, $typeId) => $q->where('leave_type_id', $typeId))
            ->orderBy('year', 'desc')
            ->orderBy('user_id')
            ->orderBy('leave_type_id');
    }

    protected function dataTableColumns(): array
    {
        return [
            'employee_name' => fn (LeaveBalance $balance) => $balance->user->name,

            'leave_type' => fn (LeaveBalance $balance) => '<span class="badge bg-info">'
                .$balance->leaveType->code.'</span> '.$balance->leaveType->name,

            'entitlement' => fn (LeaveBalance $balance) => $balance->total_entitlement.' day'
                .($balance->total_entitlement > 1 ? 's' : ''),

            'usage' => function (LeaveBalance $balance) {
                $percentage = $balance->usage_percentage;
                $color = $percentage >= 80 ? 'bg-danger' : ($percentage >= 50 ? 'bg-warning' : 'bg-success');

                return '
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar '.$color.'" role="progressbar"
                            style="width: '.$percentage.'%"
                            aria-valuenow="'.$percentage.'" aria-valuemin="0" aria-valuemax="100">
                            '.$balance->taken_days.' / '.$balance->total_entitlement.'
                        </div>
                    </div>
                    <small class="text-muted">'.$percentage.'% used</small>
                ';
            },

            'pending' => fn (LeaveBalance $balance) => $balance->pending_days > 0
                ? '<span class="badge bg-warning">'.$balance->pending_days.' pending</span>'
                : '<span class="text-muted">—</span>',

            'remaining' => function (LeaveBalance $balance) {
                $remaining = $balance->remaining_days;
                $class = $remaining <= 2 ? 'text-danger' : ($remaining <= 5 ? 'text-warning' : 'text-success');

                return '<strong class="'.$class.'">'.$remaining.'</strong> day'.($remaining != 1 ? 's' : '');
            },

            'updated_at' => fn (LeaveBalance $balance) => $balance->updated_at->format('M d, Y H:i'),

            'updated_by' => fn (LeaveBalance $balance) => $balance->updatedBy?->name ?? '—',
        ];
    }

    protected function actionColumn($balance): string
    {
        $summaryUrl = route('leave-balances.summary', [
            'user_id' => $balance->user_id,
            'year' => $balance->year,
        ]);

        return '
            <a href="'.$summaryUrl.'" class="btn btn-sm btn-info" title="View Summary">
                <i class="bi bi-file-text"></i> Summary
            </a>
        ';
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
                'data' => 'entitlement',
                'name' => 'total_entitlement',
                'label' => 'Entitlement',
                'orderable' => true,
                'searchable' => false,
                'className' => 'text-center',
            ],
            [
                'data' => 'remaining',
                'name' => 'remaining_days',
                'label' => 'Remaining',
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
                'width' => '100',
                'orderable' => false,
                'searchable' => false,
                'className' => 'text-center',
            ],
        ];
    }
}
