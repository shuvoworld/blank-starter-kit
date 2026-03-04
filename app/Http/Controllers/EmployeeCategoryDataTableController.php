<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseDataTableController;
use App\Models\EmployeeCategory;
use Illuminate\Database\Eloquent\Builder;

class EmployeeCategoryDataTableController extends BaseDataTableController
{
    public function __construct()
    {
        $this->model = EmployeeCategory::class;
        $this->routePrefix = 'employee-categories';
    }

    protected function indexQuery(): Builder
    {
        return EmployeeCategory::query()
            ->with(['updatedBy'])
            ->orderBy('name');
    }

    protected function dataTableColumns(): array
    {
        return [
            'updated_by_name' => fn (EmployeeCategory $employeeCategory) => $employeeCategory->updatedBy?->name ?? '—',
        ];
    }

    protected function actionColumn($employeeCategory): string
    {
        $showUrl = route('employee-categories.show', $employeeCategory);
        $editUrl = route('employee-categories.edit', $employeeCategory);
        $deleteUrl = route('employee-categories.destroy', $employeeCategory);

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
                    data-name="'.e($employeeCategory->name).'" title="Delete">
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
                'data' => 'updated_at',
                'name' => 'updated_at',
                'label' => 'Last Updated',
                'orderable' => true,
                'searchable' => false,
                'className' => 'text-center',
            ],
            [
                'data' => 'updated_by_name',
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
