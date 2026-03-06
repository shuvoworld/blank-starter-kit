<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseDataTableController;
use App\Models\Role;

class RoleDataTableController extends BaseDataTableController
{
    public function __construct()
    {
        $this->model = Role::class;
        $this->routePrefix = 'roles';
        $this->rawColumns = ['name_badge', 'permissions_badges'];
    }

    protected function indexQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Role::query()->with('permissions')->withCount('users');
    }

    protected function dataTableColumns(): array
    {
        return [
            'name_badge' => fn (Role $role) => '<span class="badge bg-primary fs-6">'.$role->name.'</span>',

            'permissions_badges' => function (Role $role) {
                $permissions = $role->permissions->take(5);
                $badges = $permissions->map(fn ($p) => '<span class="badge bg-info">'.$p->name.'</span>')->implode(' ');

                if ($role->permissions->count() > 5) {
                    $badges .= ' <span class="badge bg-secondary">+'.($role->permissions->count() - 5).' more</span>';
                }

                return $badges ?: '-';
            },
        ];
    }

    protected function actionColumn($role): string
    {
        $viewUrl = route('roles.show', $role);
        $editUrl = route('roles.edit', $role);
        $canUpdate = auth()->user()?->can('roles.update');

        $actions = '<div class="btn-group btn-group-sm">';
        $actions .= '<a href="'.$viewUrl.'" class="btn btn-info" title="View"><i class="bi bi-eye"></i></a>';

        if ($canUpdate && ! $role->isSuperuser()) {
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
                'data' => 'description',
                'name' => 'description',
                'label' => 'Description',
                'orderable' => false,
            ],
            [
                'data' => 'permissions_badges',
                'name' => 'permissions',
                'label' => 'Permissions',
                'orderable' => false,
                'searchable' => false,
            ],
            [
                'data' => 'users_count',
                'name' => 'users_count',
                'label' => 'Users',
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
