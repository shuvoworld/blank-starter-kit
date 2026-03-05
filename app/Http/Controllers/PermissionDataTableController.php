<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseDataTableController;
use App\Models\Permission;

class PermissionDataTableController extends BaseDataTableController
{
    public function __construct()
    {
        $this->model = Permission::class;
        $this->routePrefix = 'permissions';
        $this->rawColumns = ['name_badge', 'module_badge'];
    }

    protected function indexQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Permission::query()->withCount('roles');
    }

    protected function dataTableColumns(): array
    {
        return [
            'name_badge' => fn (Permission $permission) => '<span class="badge bg-info">'.$permission->name.'</span>',
            'module_badge' => fn (Permission $permission) => $permission->module
                ? '<span class="badge bg-secondary">'.$permission->module.'</span>'
                : '-',
        ];
    }

    protected function actionColumn($permission): string
    {
        $viewUrl = route('permissions.show', $permission);
        $editUrl = route('permissions.edit', $permission);
        $canEdit = auth()->user()?->can('update permissions');
        $hasRoles = $permission->roles_count > 0;

        $actions = '<div class="btn-group btn-group-sm">';
        $actions .= '<a href="'.$viewUrl.'" class="btn btn-info" title="View"><i class="bi bi-eye"></i></a>';

        if ($canEdit && ! $hasRoles) {
            $actions .= '<a href="'.$editUrl.'" class="btn btn-primary" title="Edit"><i class="bi bi-pencil"></i></a>';
        }

        $actions .= '</div>';

        return $actions;
    }

    public function tableColumns(): array
    {
        return [
            [
                'data' => 'name_badge',
                'name' => 'name',
                'label' => 'Name',
            ],
            [
                'data' => 'module_badge',
                'name' => 'module',
                'label' => 'Module',
                'searchable' => false,
            ],
            [
                'data' => 'description',
                'name' => 'description',
                'label' => 'Description',
                'orderable' => false,
            ],
            [
                'data' => 'roles_count',
                'name' => 'roles_count',
                'label' => 'Roles',
                'width' => '80',
                'className' => 'text-center',
            ],
            [
                'data' => 'action',
                'name' => 'action',
                'label' => 'Actions',
                'width' => '120',
                'orderable' => false,
                'searchable' => false,
                'className' => 'text-center',
            ],
        ];
    }
}
