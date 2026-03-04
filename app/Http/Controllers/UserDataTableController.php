<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseDataTableController;
use App\Models\User;

class UserDataTableController extends BaseDataTableController
{
    public function __construct()
    {
        $this->model = User::class;
        $this->routePrefix = 'users';
        $this->rawColumns = ['roles_badges', 'updated_at', 'updated_by'];
    }

    protected function indexQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return User::query()->with(['roles', 'updatedBy'])->orderBy('name');
    }

    protected function dataTableColumns(): array
    {
        return [
            'roles_badges' => function (User $user) {
                $badges = $user->roles->take(3)->map(function ($role) {
                    $color = match ($role->name) {
                        'Superuser' => 'bg-danger',
                        'Admin' => 'bg-warning',
                        default => 'bg-primary',
                    };

                    return '<span class="badge '.$color.'">'.e($role->name).'</span>';
                })->implode(' ');

                if ($user->roles->count() > 3) {
                    $badges .= ' <span class="badge bg-secondary">+'.($user->roles->count() - 3).'</span>';
                }

                return $badges ?: '<span class="text-muted">No roles</span>';
            },

            'updated_at' => fn (User $user) => $user->updated_at->format('M d, Y H:i'),

            'updated_by' => fn (User $user) => $user->updatedBy?->name ?? '—',
        ];
    }

    protected function actionColumn($user): string
    {
        $editUrl = route('users.edit', $user);
        $deleteUrl = route('users.destroy', $user);
        $canDelete = auth()->user()?->can('delete users') && auth()->id() !== $user->id;

        $actions = '
            <div class="btn-group btn-group-sm">
                <a href="'.$editUrl.'" class="btn btn-primary" title="Edit">
                    <i class="bi bi-pencil"></i>
                </a>';

        if ($canDelete) {
            $actions .= '
                <button type="button" class="btn btn-danger btn-delete"
                    data-url="'.$deleteUrl.'"
                    data-name="'.e($user->name).'" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>';
        }

        $actions .= '
            </div>
        ';

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
                'data' => 'roles_badges',
                'name' => 'roles',
                'label' => 'Roles',
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
                'width' => '120',
                'orderable' => false,
                'searchable' => false,
                'className' => 'text-center',
            ],
        ];
    }
}
