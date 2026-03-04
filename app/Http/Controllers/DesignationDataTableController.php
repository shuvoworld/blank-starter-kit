<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseDataTableController;
use App\Models\Designation;

class DesignationDataTableController extends BaseDataTableController
{
    public function __construct()
    {
        $this->model = Designation::class;
        $this->routePrefix = 'designations';
        $this->rawColumns = ['status_badge', 'updated_at', 'updated_by'];
    }

    protected function indexQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Designation::query()->with('updatedBy')->byStatus(request()->input('filter_status'));
    }

    protected function dataTableColumns(): array
    {
        return [
            'status_badge' => fn (Designation $designation) => '<span class="badge '.($designation->is_active ? 'bg-success' : 'bg-secondary').'">'.($designation->is_active ? 'Active' : 'Inactive').'</span>',

            'updated_at' => fn (Designation $designation) => $designation->updated_at->format('M d, Y H:i'),

            'updated_by' => fn (Designation $designation) => $designation->updatedBy?->name ?? '—',
        ];
    }

    protected function actionColumn($designation): string
    {
        $showUrl = route('designations.show', $designation);
        $editUrl = route('designations.edit', $designation);
        $deleteUrl = route('designations.destroy', $designation);

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
                    data-name="'.e($designation->name).'" title="Delete">
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
