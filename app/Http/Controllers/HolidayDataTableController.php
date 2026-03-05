<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseDataTableController;
use App\Models\Holiday;

class HolidayDataTableController extends BaseDataTableController
{
    public function __construct()
    {
        $this->model = Holiday::class;
        $this->routePrefix = 'holidays';
        $this->rawColumns = ['date_formatted', 'type_badge', 'location', 'recurring_badge', 'status_badge', 'updated_at', 'updated_by'];
    }

    protected function indexQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Holiday::query()->with(['country', 'city', 'updatedBy'])->orderBy('date');
    }

    protected function dataTableColumns(): array
    {
        return [
            'date_formatted' => fn (Holiday $holiday) => $holiday->date->format('M d, Y'),

            'type_badge' => fn (Holiday $holiday) => '<span class="badge '.($holiday->holiday_type === 'global' ? 'bg-primary' : 'bg-info').'">'
                .ucfirst($holiday->holiday_type).'</span>',

            'location' => function (Holiday $holiday) {
                if ($holiday->holiday_type === 'global') {
                    return '<span class="text-muted">—</span>';
                }

                $location = [];
                if ($holiday->country) {
                    $location[] = $holiday->country->name;
                }
                if ($holiday->city) {
                    $location[] = $holiday->city->name;
                }

                return $location ? implode(', ', $location) : '<span class="text-muted">—</span>';
            },

            'recurring_badge' => fn (Holiday $holiday) => $holiday->is_recurring
                ? '<span class="badge bg-success"><i class="bi bi-arrow-repeat me-1"></i>Yes</span>'
                : '<span class="text-muted">No</span>',

            'status_badge' => fn (Holiday $holiday) => '<span class="badge '.($holiday->is_active ? 'bg-success' : 'bg-secondary').'">'
                .($holiday->is_active ? 'Active' : 'Inactive').'</span>',

            'updated_at' => fn (Holiday $holiday) => $holiday->updated_at->format('M d, Y H:i'),

            'updated_by' => fn (Holiday $holiday) => $holiday->updatedBy?->name ?? '—',
        ];
    }

    protected function actionColumn($holiday): string
    {
        $showUrl = route('holidays.show', $holiday);
        $editUrl = route('holidays.edit', $holiday);
        $deleteUrl = route('holidays.destroy', $holiday);

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
                    data-name="'.e($holiday->name).'" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
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
                'data' => 'name',
                'name' => 'name',
                'label' => 'Name',
            ],
            [
                'data' => 'date_formatted',
                'name' => 'date',
                'label' => 'Date',
                'width' => '120',
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
                'width' => '150',
                'orderable' => false,
                'searchable' => false,
                'className' => 'text-center',
            ],
        ];
    }
}
