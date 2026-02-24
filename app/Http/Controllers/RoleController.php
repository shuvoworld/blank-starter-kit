<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignPermissionsToRoleRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::of(Role::query()->with('permissions')->withCount('users'))
                ->editColumn('name', function ($role) {
                    return '<span class="badge bg-primary fs-6">'.$role->name.'</span>';
                })
                ->editColumn('description', function ($role) {
                    return $role->description ?? '-';
                })
                ->addColumn('permissions', function ($role) {
                    $permissions = $role->permissions->take(5);
                    $badges = $permissions->map(function ($permission) {
                        return '<span class="badge bg-info">'.$permission->name.'</span>';
                    })->implode(' ');

                    if ($role->permissions->count() > 5) {
                        $badges .= ' <span class="badge bg-secondary">+'.($role->permissions->count() - 5).' more</span>';
                    }

                    return $badges ?: '-';
                })
                ->addColumn('users_count', function ($role) {
                    return $role->users_count;
                })
                ->addColumn('action', function ($role) {
                    $viewUrl = route('roles.show', $role);
                    $editUrl = route('roles.edit', $role);
                    $canUpdate = auth()->user()?->can('update roles');

                    $actions = '<div class="btn-group btn-group-sm">';
                    $actions .= '<a href="'.$viewUrl.'" class="btn btn-info" title="View"><i class="bi bi-eye"></i></a>';

                    if ($canUpdate && ! $role->isSuperAdmin()) {
                        $actions .= '<a href="'.$editUrl.'" class="btn btn-primary" title="Edit"><i class="bi bi-pencil"></i></a>';
                    }

                    $actions .= '</div>';

                    return $actions;
                })
                ->rawColumns(['name', 'permissions', 'action'])
                ->make(true);
        }

        return view('roles.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $permissions = Permission::all()->groupBy('module');

        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create([
            'name' => $request->input('name'),
            'guard_name' => $request->input('guard_name', 'web'),
            'description' => $request->input('description'),
        ]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        return to_route('roles.index')
            ->with('status', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): View
    {
        $role->load('permissions', 'users');

        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): View
    {
        $role->load('permissions');
        $permissions = Permission::all()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $role->update([
            'name' => $request->input('name', $role->name),
            'guard_name' => $request->input('guard_name', $role->guard_name),
            'description' => $request->input('description', $role->description),
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        return to_route('roles.index')
            ->with('status', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        // Prevent deletion of Super Admin role
        if ($role->isSuperAdmin()) {
            return back()->with('error', 'Cannot delete Super Admin role.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role with assigned users.');
        }

        $role->delete();

        return to_route('roles.index')
            ->with('status', 'Role deleted successfully.');
    }

    /**
     * Assign permissions to a role.
     */
    public function assignPermissions(AssignPermissionsToRoleRequest $request, Role $role): RedirectResponse
    {
        $role->syncPermissions($request->input('permissions'));

        return back()->with('status', 'Permissions assigned successfully.');
    }

    /**
     * Remove a permission from a role.
     */
    public function removePermission(Role $role, Permission $permission): RedirectResponse
    {
        $role->revokePermissionTo($permission);

        return back()->with('status', 'Permission removed successfully.');
    }
}
