<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
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
    public function getWithoutSuperAdmin(): Collection
    {
        return Role::withoutSuperAdmin()->get();
    }

    /**
     * Create a new role.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
            'description' => $data['description'] ?? null,
        ]);

        if (! empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    /**
     * Update an existing role.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Role $role, array $data): Role
    {
        $role->update([
            'name' => $data['name'] ?? $role->name,
            'guard_name' => $data['guard_name'] ?? $role->guard_name,
            'description' => $data['description'] ?? $role->description,
        ]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->fresh();
    }

    /**
     * Delete a role.
     */
    public function delete(Role $role): bool
    {
        if ($role->isSuperAdmin()) {
            throw new \Exception('Cannot delete Super Admin role.');
        }

        if ($role->users()->count() > 0) {
            throw new \Exception('Cannot delete role with assigned users.');
        }

        return $role->delete();
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
    public function getOrCreateSuperAdmin(): Role
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
    public function syncSuperAdminPermissions(): void
    {
        $superAdmin = $this->getOrCreateSuperAdmin();
        $allPermissions = Permission::all()->pluck('id');
        $superAdmin->syncPermissions($allPermissions);
    }

    /**
     * Check if a role can be modified.
     */
    public function canModify(Role $role): bool
    {
        return ! $role->isSuperAdmin();
    }

    /**
     * Get role count by users.
     */
    public function getUserCount(Role $role): int
    {
        return $role->users()->count();
    }
}
