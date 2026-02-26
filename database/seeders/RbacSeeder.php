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

        // Create permissions for schedule management system
        $modules = [
            'users' => ['view any', 'view', 'create', 'update', 'delete'],
            'roles' => ['view any', 'view', 'create', 'update', 'delete'],
            'permissions' => ['view any', 'view'],
            'employees' => ['view any', 'view', 'create', 'update', 'delete'],
            'schedules' => ['view any', 'view', 'create', 'update', 'delete', 'assign'],
            'shifts' => ['view any', 'view', 'create', 'update', 'delete'],
            'departments' => ['view any', 'view', 'create', 'update', 'delete'],
            'designations' => ['view any', 'view', 'create', 'update', 'delete'],
            'leave types' => ['view any', 'view', 'create', 'update', 'delete'],
            'holidays' => ['view any', 'view', 'create', 'update', 'delete'],
            'leave requests' => ['view any', 'view', 'create', 'approve', 'reject', 'cancel'],
            'leave balances' => ['view any', 'view'],
            'dashboard' => ['view'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(
                    [
                        'name' => "{$action} {$module}",
                        'guard_name' => 'web',
                    ],
                    [
                        'module' => $module,
                        'description' => ucfirst($action).' '.ucfirst($module),
                    ]
                );
            }
        }

        // Create Superuser role with all permissions
        $superuser = Role::firstOrCreate(
            ['name' => 'Superuser', 'guard_name' => 'web'],
            ['description' => 'Full system access with all permissions']
        );
        $superuser->syncPermissions(Permission::all());

        // Create Admin role with management permissions (except delete users/roles)
        $admin = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['description' => 'Administrator with schedule and employee management permissions']
        );
        $admin->syncPermissions(
            Permission::where('name', 'not like', '%delete users%')
                ->where('name', 'not like', '%delete roles%')
                ->get()
        );

        // Create Employee role with limited permissions
        $employee = Role::firstOrCreate(
            ['name' => 'Employee', 'guard_name' => 'web'],
            ['description' => 'Employee with view-only access to own schedule']
        );
        $employee->syncPermissions(
            Permission::whereIn('module', ['dashboard', 'employees', 'departments', 'designations', 'holidays', 'leave requests', 'leave balances'])
                ->where(function ($query) {
                    $query->where('name', 'view dashboard')
                        ->orWhere('name', 'view any employees')
                        ->orWhere('name', 'view any departments')
                        ->orWhere('name', 'view any designations')
                        ->orWhere('name', 'view any holidays')
                        ->orWhere('name', 'create leave requests')
                        ->orWhere('name', 'view leave balances');
                })
                ->get()
        );

        $this->command->info('RBAC seeded successfully.');
        $this->command->info('Roles created: Superuser, Admin, Employee');
        $this->command->info('Permissions created for modules: users, roles, permissions, employees, schedules, shifts, departments, designations, leave types, holidays, leave requests, leave balances, dashboard');
    }
}
