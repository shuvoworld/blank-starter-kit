<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for each module
        $modules = [
            'users' => ['view any', 'view', 'create', 'update', 'delete', 'restore'],
            'roles' => ['view any', 'view', 'create', 'update', 'delete', 'restore'],
            'permissions' => ['view any', 'view', 'create', 'update', 'delete'],
            'posts' => ['view any', 'view', 'create', 'update', 'delete', 'restore', 'force delete'],
            'categories' => ['view any', 'view', 'create', 'update', 'delete', 'restore'],
            'settings' => ['view any', 'view', 'update'],
            'dashboard' => ['view'],
            'landing page' => ['manage'],
        ];

        $permissionsMap = [];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissionName = "{$action} {$module}";
                $permission = Permission::firstOrCreate(
                    [
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ],
                    [
                        'module' => $module,
                        'description' => ucfirst($action).' '.ucfirst($module),
                    ]
                );
                $permissionsMap[$module][] = $permission->id;
            }
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            ['description' => 'Super Administrator with all permissions']
        );
        $superAdmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['description' => 'Administrator with most permissions']
        );
        // Admin has all permissions except delete/force delete
        $admin->syncPermissions(
            Permission::where('name', 'not like', '%delete%')
                ->where('name', 'not like', '%force%')
                ->get()
        );

        $manager = Role::firstOrCreate(
            ['name' => 'Manager', 'guard_name' => 'web'],
            ['description' => 'Manager with content management permissions']
        );
        $manager->syncPermissions(
            Permission::whereIn('module', ['posts', 'categories'])
                ->where('name', 'not like', '%delete%')
                ->where('name', 'not like', '%restore%')
                ->get()
        );

        $editor = Role::firstOrCreate(
            ['name' => 'Editor', 'guard_name' => 'web'],
            ['description' => 'Editor with content editing permissions']
        );
        $editor->syncPermissions(
            Permission::where('module', 'posts')
                ->whereIn('name', ['view posts', 'view any posts', 'create posts', 'update posts'])
                ->get()
        );

        $user = Role::firstOrCreate(
            ['name' => 'User', 'guard_name' => 'web'],
            ['description' => 'Standard user with basic permissions']
        );
        $user->syncPermissions([
            Permission::where('name', 'view dashboard')->first(),
        ]);

        $this->command->info('RBAC seeded successfully.');
        $this->command->info('Roles created: Super Admin, Admin, Manager, Editor, User');
        $this->command->info('Permissions created for modules: users, roles, permissions, posts, categories, settings, dashboard');
    }
}
