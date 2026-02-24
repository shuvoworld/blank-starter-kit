<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::of(Permission::query()->withCount('roles'))
                ->editColumn('name', function ($permission) {
                    return '<span class="badge bg-info">'.$permission->name.'</span>';
                })
                ->editColumn('module', function ($permission) {
                    return $permission->module
                        ? '<span class="badge bg-secondary">'.$permission->module.'</span>'
                        : '-';
                })
                ->addColumn('roles_count', function ($permission) {
                    return $permission->roles_count;
                })
                ->editColumn('description', function ($permission) {
                    return $permission->description ?? '-';
                })
                ->addColumn('action', function ($permission) {
                    $viewUrl = route('permissions.show', $permission);
                    $editUrl = route('permissions.edit', $permission);
                    $canEdit = auth()->user()?->can('update permissions');
                    $hasRoles = $permission->roles_count > 0;

                    $actions = '<div class="btn-group btn-group-sm">';
                    $actions .= '<a href="'.$viewUrl.'" class="btn btn-info" title="View"><i class="bi bi-eye"></i></a>';

                    if ($canEdit) {
                        if (! $hasRoles) {
                            $actions .= '<a href="'.$editUrl.'" class="btn btn-primary" title="Edit"><i class="bi bi-pencil"></i></a>';
                        }
                    }

                    $actions .= '</div>';

                    return $actions;
                })
                ->rawColumns(['name', 'module', 'action'])
                ->make(true);
        }

        $modules = Permission::select('module')->distinct()->pluck('module')->filter();

        return view('permissions.index', compact('modules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $modules = Permission::select('module')->distinct()->pluck('module')->filter();

        return view('permissions.create', compact('modules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request): RedirectResponse
    {
        Permission::create([
            'name' => $request->input('name'),
            'guard_name' => $request->input('guard_name', 'web'),
            'module' => $request->input('module'),
            'description' => $request->input('description'),
        ]);

        return to_route('permissions.index')
            ->with('status', 'Permission created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission): View
    {
        $permission->load('roles');

        return view('permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission): View
    {
        $modules = Permission::select('module')->distinct()->pluck('module')->filter();

        return view('permissions.edit', compact('permission', 'modules'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $permission->update([
            'name' => $request->input('name', $permission->name),
            'guard_name' => $request->input('guard_name', $permission->guard_name),
            'module' => $request->input('module', $permission->module),
            'description' => $request->input('description', $permission->description),
        ]);

        return to_route('permissions.index')
            ->with('status', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        // Check if permission is assigned to any role
        if ($permission->roles()->count() > 0) {
            return back()->with('error', 'Cannot delete permission assigned to roles.');
        }

        $permission->delete();

        return to_route('permissions.index')
            ->with('status', 'Permission deleted successfully.');
    }

    /**
     * Get permissions by module for API calls.
     */
    public function getByModule(Request $request): array
    {
        $permissions = Permission::query()
            ->when($request->filled('module'), function ($query) use ($request) {
                $query->where('module', $request->input('module'));
            })
            ->get()
            ->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'module' => $permission->module,
                ];
            });

        return ['permissions' => $permissions];
    }
}
