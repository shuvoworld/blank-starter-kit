<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class RbacService
{
    public function __construct(
        protected PermissionService $permissionService,
        protected RoleService $roleService,
    ) {}

    /**
     * Check if user has a specific permission.
     */
    public function userHasPermission(User $user, string $permission): bool
    {
        return $user->hasPermissionTo($permission);
    }

    /**
     * Check if user has any of the given permissions.
     *
     * @param  array<int, string>  $permissions
     */
    public function userHasAnyPermission(User $user, array $permissions): bool
    {
        return $user->hasAnyPermission($permissions);
    }

    /**
     * Check if user has all of the given permissions.
     *
     * @param  array<int, string>  $permissions
     */
    public function userHasAllPermissions(User $user, array $permissions): bool
    {
        return $user->hasAllPermissions($permissions);
    }

    /**
     * Check if user has a specific role.
     */
    public function userHasRole(User $user, string $role): bool
    {
        return $user->hasRole($role);
    }

    /**
     * Check if user has any of the given roles.
     *
     * @param  array<int, string>  $roles
     */
    public function userHasAnyRole(User $user, array $roles): bool
    {
        return $user->hasAnyRole($roles);
    }

    /**
     * Check if user has all of the given roles.
     *
     * @param  array<int, string>  $roles
     */
    public function userHasAllRoles(User $user, array $roles): bool
    {
        return $user->hasAllRoles($roles);
    }

    /**
     * Check if user is superuser.
     */
    public function isSuperuser(User $user): bool
    {
        return $user->hasRole('Superuser');
    }

    /**
     * Assign roles to a user.
     *
     * @param  array<int, int>  $roleIds
     */
    public function assignRolesToUser(User $user, array $roleIds): void
    {
        $user->syncRoles($roleIds);
    }

    /**
     * Get all permissions for a user (direct + via roles).
     *
     * @return Collection<int, Permission>
     */
    public function getUserPermissions(User $user): Collection
    {
        return $user->getAllPermissions();
    }

    /**
     * Get all roles for a user.
     *
     * @return Collection<int, Role>
     */
    public function getUserRoles(User $user): Collection
    {
        return $user->roles;
    }

    /**
     * Get all permissions grouped by module for a user.
     *
     * @return array<string, Collection<int, Permission>>
     */
    public function getUserPermissionsGrouped(User $user): array
    {
        return $this->getUserPermissions($user)->groupBy('module')->toArray();
    }

    /**
     * Initialize default RBAC structure.
     * Creates Super Admin role and assigns all permissions.
     */
    public function initializeDefaultRbac(): void
    {
        $this->roleService->getOrCreateSuperuser();
    }

    /**
     * Check if a permission exists in the system.
     */
    public function permissionExists(string $name): bool
    {
        return $this->permissionService->exists($name);
    }

    /**
     * Check if a role exists in the system.
     */
    public function roleExists(string $name): bool
    {
        return Role::where('name', $name)->exists();
    }

    /**
     * Get a list of all modules that have permissions.
     *
     * @return Collection<int, string>
     */
    public function getModules(): Collection
    {
        return $this->permissionService->getModules();
    }

    /**
     * Get all permissions for a specific module.
     *
     * @return Collection<int, Permission>
     */
    public function getModulePermissions(string $module): Collection
    {
        return $this->permissionService->getByModule($module);
    }

    /**
     * Generate standard CRUD permissions for a module.
     *
     * @return array<int, Permission>
     */
    public function generateModulePermissions(string $module): array
    {
        return $this->permissionService->generateForModule($module);
    }

    /**
     * Sync all permissions to Super Admin role.
     */
    public function syncSuperuserAllPermissions(): void
    {
        $this->roleService->syncSuperuserPermissions();
    }
}
