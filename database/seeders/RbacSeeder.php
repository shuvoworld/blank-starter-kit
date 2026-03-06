<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'users' => ['view', 'create', 'update', 'delete', 'manage-roles'],
            'roles' => ['view', 'create', 'update', 'delete'],
            'permissions' => ['view', 'update'],
            'employees' => ['view', 'create', 'update', 'delete'],
            'schedules' => ['view', 'create', 'update', 'delete', 'assign'],
            'shifts' => ['view', 'create', 'update', 'delete'],
            'departments' => ['view', 'create', 'update', 'delete'],
            'designations' => ['view', 'create', 'update', 'delete'],
            'leave-types' => ['view', 'create', 'update', 'delete'],
            'holidays' => ['view', 'create', 'update', 'delete'],
            'leave-requests' => ['view', 'create', 'update', 'delete', 'approve', 'reject', 'cancel'],
            'leave-balances' => ['view', 'create', 'update', 'delete'],
            'employee-categories' => ['view', 'create', 'update', 'delete'],
            'products' => ['view', 'create', 'update', 'delete'],
            'landing-page-sections' => ['view', 'create', 'update', 'delete'],
            'dashboard' => ['view'],
            'activity-log' => ['view'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(
                    ['name' => "{$module}.{$action}", 'guard_name' => 'web'],
                    ['module' => $module, 'description' => ucfirst($module).' — '.ucfirst($action)]
                );
            }
        }

        $superuser = Role::firstOrCreate(
            ['name' => 'Superuser', 'guard_name' => 'web'],
            ['description' => 'Full system access with all permissions']
        );
        $superuser->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['description' => 'Administrator with schedule and employee management permissions']
        );
        $admin->syncPermissions(
            Permission::where('name', 'not like', '%users.delete%')
                ->where('name', 'not like', '%roles.delete%')
                ->get()
        );

        $employee = Role::firstOrCreate(
            ['name' => 'Employee', 'guard_name' => 'web'],
            ['description' => 'Employee with limited self-service access']
        );
        $employee->syncPermissions(
            Permission::whereIn('name', [
                'dashboard.view',
                'employees.view',
                'departments.view',
                'designations.view',
                'holidays.view',
                'leave-requests.create',
                'leave-balances.view',
            ])->get()
        );

        $this->command->info('RBAC seeded successfully.');
        $this->command->info('Roles created: Superuser, Admin, Employee');
    }
}
