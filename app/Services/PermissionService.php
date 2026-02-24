<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionService
{
    /**
     * Get all permissions grouped by module.
     *
     * @return Collection<int, Permission>
     */
    public function getAllGroupedByModule(): Collection
    {
        return Permission::all()->groupBy('module');
    }

    /**
     * Get permissions by module.
     *
     * @return Collection<int, Permission>
     */
    public function getByModule(string $module): Collection
    {
        return Permission::byModule($module)->get();
    }

    /**
     * Get all unique module names.
     *
     * @return Collection<int, string>
     */
    public function getModules(): Collection
    {
        return Permission::select('module')
            ->distinct()
            ->pluck('module')
            ->filter();
    }

    /**
     * Create a new permission.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Permission
    {
        return Permission::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
            'module' => $data['module'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Update an existing permission.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Permission $permission, array $data): Permission
    {
        $permission->update([
            'name' => $data['name'] ?? $permission->name,
            'guard_name' => $data['guard_name'] ?? $permission->guard_name,
            'module' => $data['module'] ?? $permission->module,
            'description' => $data['description'] ?? $permission->description,
        ]);

        return $permission->fresh();
    }

    /**
     * Delete a permission.
     */
    public function delete(Permission $permission): bool
    {
        if ($permission->roles()->count() > 0) {
            throw new \Exception('Cannot delete permission assigned to roles.');
        }

        return $permission->delete();
    }

    /**
     * Check if a permission exists.
     */
    public function exists(string $name, string $guardName = 'web'): bool
    {
        return Permission::where('name', $name)
            ->where('guard_name', $guardName)
            ->exists();
    }

    /**
     * Find permission by name.
     */
    public function findByName(string $name, string $guardName = 'web'): ?Permission
    {
        return Permission::where('name', $name)
            ->where('guard_name', $guardName)
            ->first();
    }

    /**
     * Sync permissions for a model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array<int, int>  $permissionIds
     */
    public function syncToModel($model, array $permissionIds): void
    {
        $model->syncPermissions($permissionIds);
    }

    /**
     * Get permission names for display.
     *
     * @return array<string, string>
     */
    public function getPermissionLabels(): array
    {
        return [
            'view any' => 'View Any',
            'view' => 'View',
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
            'restore' => 'Restore',
            'force delete' => 'Force Delete',
        ];
    }

    /**
     * Generate permissions for a module.
     *
     * @return array<int, Permission>
     */
    public function generateForModule(string $module): array
    {
        $actions = ['view any', 'view', 'create', 'update', 'delete'];
        $permissions = [];

        foreach ($actions as $action) {
            $permissionName = "{$action} {$module}";
            if (! $this->exists($permissionName)) {
                $permissions[] = $this->create([
                    'name' => $permissionName,
                    'module' => $module,
                    'description' => ucfirst($action)." {$module}",
                ]);
            }
        }

        return $permissions;
    }
}
