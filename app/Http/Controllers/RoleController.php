<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\AssignPermissionsToRoleRequest;
use App\Http\Requests\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends BaseController
{
    public function __construct(RoleDataTableController $dataTableController, RoleService $service)
    {
        $this->model = Role::class;
        $this->routePrefix = 'roles';
        $this->viewPrefix = 'roles';
        $this->resourceName = 'Role';
        $this->dataTableController = $dataTableController;
        $this->service = $service;
    }

    protected function requestClass(): ?string
    {
        return RoleRequest::class;
    }

    protected function createViewData(): array
    {
        return ['permissions' => Permission::all()->groupBy('module')];
    }

    protected function editViewData(Model $record): array
    {
        $record->load('permissions');

        return [
            'permissions' => Permission::all()->groupBy('module'),
            'rolePermissions' => $record->permissions->pluck('id')->toArray(),
        ];
    }

    public function show(int|string $record): View
    {
        $role = $this->findRecord($record);
        $role->load('permissions', 'users');

        return view('roles.show', compact('role'));
    }

    protected function beforeDestroy(Model $record): void
    {
        abort_if($record->isSuperuser(), 403, 'Cannot delete Superuser role.');
        abort_if($record->users()->count() > 0, 422, 'Cannot delete role with assigned users.');
    }

    /**
     * Assign permissions to a role.
     */
    public function assignPermissions(AssignPermissionsToRoleRequest $request, int|string $record): RedirectResponse
    {
        $role = $this->findRecord($record);
        $role->syncPermissions(
            Permission::whereIn('id', $request->input('permissions', []))->get()
        );

        return back()->with('status', 'Permissions assigned successfully.');
    }

    /**
     * Remove a permission from a role.
     */
    public function removePermission(int|string $record, Permission $permission): RedirectResponse
    {
        $role = $this->findRecord($record);
        $role->revokePermissionTo($permission);

        return back()->with('status', 'Permission removed successfully.');
    }
}
