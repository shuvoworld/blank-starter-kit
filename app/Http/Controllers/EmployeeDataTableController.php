<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseDataTableController;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

class EmployeeDataTableController extends BaseDataTableController
{
    public function __construct()
    {
        $this->model = Employee::class;
        $this->routePrefix = 'employees';
        $this->withRelations = ['user', 'departmentRelation', 'designation', 'country', 'city', 'area'];
        $this->rawColumns = ['profile_picture', 'user_badge', 'status_badge'];
    }

    protected function indexQuery(): Builder
    {
        $request = request();

        return Employee::query()
            ->with($this->withRelations)
            ->byDepartmentId($request->input('filter_department_id'))
            ->byDesignationId($request->input('filter_designation_id'))
            ->byCountry($request->input('filter_country_id'))
            ->byCity($request->input('filter_city_id'))
            ->byArea($request->input('filter_area_id'))
            ->byStatus($request->input('filter_status'))
            ->byHireDateRange(
                $request->input('filter_hire_date_from'),
                $request->input('filter_hire_date_to')
            );
    }

    protected function dataTableColumns(): array
    {
        return [
            'profile_picture' => fn (Employee $employee) => $this->renderProfilePicture($employee),

            'user_badge' => fn (Employee $employee) => $employee->user
                ? '<span class="badge bg-primary"><i class="bi bi-person-check me-1"></i>'.e($employee->user->name).'</span>'
                : '<span class="text-muted">—</span>',

            'department_name' => fn (Employee $employee) => $employee->departmentRelation?->name ?? $employee->department ?? '—',

            'designation_name' => fn (Employee $employee) => $employee->designation?->name ?? $employee->position ?? '—',

            'location' => fn (Employee $employee) => implode(', ', array_filter([
                $employee->area?->name,
                $employee->city?->name,
                $employee->country?->name,
            ])) ?: '—',

            'status_badge' => fn (Employee $employee) => '<span class="badge '.($employee->status === 'active' ? 'bg-success' : 'bg-secondary').'">'.ucfirst($employee->status).'</span>',
        ];
    }

    private function renderProfilePicture(Employee $employee): string
    {
        $url = $employee->getFirstMediaUrl('profile_picture', 'thumb');

        if ($url) {
            return '<img src="'.$url.'" alt="'.e($employee->name).'" class="rounded-circle" width="40" height="40" style="object-fit: cover;">';
        }

        return '<div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px; font-size: 1.2rem;"><i class="bi bi-person-fill"></i></div>';
    }

    protected function actionColumn($employee): string
    {
        $showUrl = route('employees.show', $employee);
        $editUrl = route('employees.edit', $employee);
        $deleteUrl = route('employees.destroy', $employee);

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
                    data-name="'.e($employee->name).'" title="Delete">
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
                'data' => 'profile_picture',
                'name' => 'profile_picture',
                'label' => 'Photo',
                'width' => '60',
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
                'data' => 'email',
                'name' => 'email',
                'label' => 'Email',
            ],
            [
                'data' => 'user_badge',
                'name' => 'user_badge',
                'label' => 'System User',
                'orderable' => false,
                'searchable' => false,
            ],
            [
                'data' => 'department_name',
                'name' => 'department_name',
                'label' => 'Department',
            ],
            [
                'data' => 'designation_name',
                'name' => 'designation_name',
                'label' => 'Designation',
            ],
            [
                'data' => 'status_badge',
                'name' => 'status',
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
