<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Services\BaseService\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class RoleService extends BaseService
{
    protected string $modelClass = Role::class;

    /**
     * Get all roles with their permissions.
     *
     * @return Collection<int, Role>
     */
    public function getAllWithPermissions(): Collection
    {
        return Role::with('permissions')->get();
    }

    /**
     * Get roles without Super Admin.
     *
     * @return Collection<int, Role>
     */
    public function getWithoutSuperuser(): Collection
    {
        return Role::withoutSuperuser()->get();
    }

    protected function afterCreate(Model $record, array $data): void
    {
        if (! empty($data['permissions'])) {
            $record->syncPermissions($data['permissions']);
        }
    }

    protected function afterUpdate(Model $record, array $data): void
    {
        if (array_key_exists('permissions', $data)) {
            $record->syncPermissions($data['permissions'] ?? []);
        }
    }

    /**
     * Find role by name.
     */
    public function findByName(string $name, string $guardName = 'web'): ?Role
    {
        return Role::where('name', $name)
            ->where('guard_name', $guardName)
            ->first();
    }

    /**
     * Assign permissions to a role.
     *
     * @param  array<int, int>  $permissionIds
     */
    public function assignPermissions(Role $role, array $permissionIds): void
    {
        $role->syncPermissions($permissionIds);
    }

    /**
     * Remove a permission from a role.
     */
    public function removePermission(Role $role, Permission $permission): void
    {
        $role->revokePermissionTo($permission);
    }

    /**
     * Check if a role has a specific permission.
     */
    public function hasPermission(Role $role, string $permission): bool
    {
        return $role->hasPermissionTo($permission);
    }

    /**
     * Get all permissions for a role.
     *
     * @return Collection<int, Permission>
     */
    public function getPermissions(Role $role): Collection
    {
        return $role->permissions;
    }

    /**
     * Get all users for a role.
     *
     * @return Collection<int, \App\Models\User>
     */
    public function getUsers(Role $role): Collection
    {
        return $role->users;
    }

    /**
     * Get role by ID with permissions and users.
     */
    public function getWithRelations(int $roleId): ?Role
    {
        return Role::with('permissions', 'users')->find($roleId);
    }

    /**
     * Create or get the Superuser role.
     */
    public function getOrCreateSuperuser(): Role
    {
        return Role::firstOrCreate(
            ['name' => 'Superuser'],
            [
                'guard_name' => 'web',
                'description' => 'Full system access with all permissions',
            ]
        );
    }

    /**
     * Give all permissions to Super Admin role.
     */
    public function syncSuperuserPermissions(): void
    {
        $superAdmin = $this->getOrCreateSuperuser();
        $allPermissions = Permission::all()->pluck('id');
        $superAdmin->syncPermissions($allPermissions);
    }

    /**
     * Check if a role can be modified.
     */
    public function canModify(Role $role): bool
    {
        return ! $role->isSuperuser();
    }

    /**
     * Get role count by users.
     */
    public function getUserCount(Role $role): int
    {
        return $role->users()->count();
    }
}
