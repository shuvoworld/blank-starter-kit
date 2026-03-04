<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\AssignPermissionsToRoleRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends BaseController
{
    public function __construct(RoleDataTableController $dataTableController)
    {
        $this->model = Role::class;
        $this->routePrefix = 'roles';
        $this->viewPrefix = 'roles';
        $this->resourceName = 'Role';
        $this->dataTableController = $dataTableController;
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

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create([
            'name' => $request->input('name'),
            'guard_name' => $request->input('guard_name', 'web'),
            'description' => $request->input('description'),
        ]);

        if ($request->filled('permissions')) {
            $role->syncPermissions(
                Permission::whereIn('id', $request->input('permissions'))->get()
            );
        }

        return $this->successRedirect('created');
    }

    public function update(UpdateRoleRequest $request, int|string $record): RedirectResponse
    {
        $role = $this->findRecord($record);

        $role->update([
            'name' => $request->input('name', $role->name),
            'guard_name' => $request->input('guard_name', $role->guard_name),
            'description' => $request->input('description', $role->description),
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions(
                Permission::whereIn('id', $request->input('permissions', []))->get()
            );
        }

        return $this->successRedirect('updated');
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
