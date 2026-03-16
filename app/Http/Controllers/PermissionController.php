<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\PermissionRequest;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends BaseController
{
    public function __construct(PermissionDataTableController $dataTableController)
    {
        $this->model = Permission::class;
        $this->routePrefix = 'permissions';
        $this->viewPrefix = 'permissions';
        $this->resourceName = 'Permission';
        $this->dataTableController = $dataTableController;
    }

    protected function createViewData(): array
    {
        return ['modules' => Permission::select('module')->distinct()->pluck('module')->filter()];
    }

    protected function editViewData(Model $record): array
    {
        return ['modules' => Permission::select('module')->distinct()->pluck('module')->filter()];
    }

    protected function requestClass(): ?string
    {
        return PermissionRequest::class;
    }

    public function show(int|string $record): View
    {
        $permission = $this->findRecord($record);
        $permission->load('roles');

        return view('permissions.show', compact('permission'));
    }

    protected function beforeDestroy(Model $record): void
    {
        abort_if($record->roles()->count() > 0, 422, 'Cannot delete permission assigned to roles.');
    }

    /**
     * Get permissions by module for API calls.
     */
    public function getByModule(Request $request): JsonResponse
    {
        $permissions = Permission::query()
            ->when($request->filled('module'), fn ($q) => $q->where('module', $request->input('module')))
            ->get()
            ->map(fn ($permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
                'module' => $permission->module,
            ]);

        return response()->json(['permissions' => $permissions]);
    }
}
