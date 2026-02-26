<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clear cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Delete old roles that are no longer needed
        $rolesToDelete = ['Manager', 'Editor', 'User'];
        Role::whereIn('name', $rolesToDelete)->delete();

        // Rename 'Super Admin' to 'Superuser' if it exists, otherwise create Superuser
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->update(['name' => 'Superuser', 'description' => 'Full system access with all permissions']);
        } else {
            Role::firstOrCreate(
                ['name' => 'Superuser', 'guard_name' => 'web'],
                ['description' => 'Full system access with all permissions']
            );
        }

        // Update Admin role description
        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $admin->update(['description' => 'Administrator with schedule and employee management permissions']);
        } else {
            Role::firstOrCreate(
                ['name' => 'Admin', 'guard_name' => 'web'],
                ['description' => 'Administrator with schedule and employee management permissions']
            );
        }

        // Create Employee role
        Role::firstOrCreate(
            ['name' => 'Employee', 'guard_name' => 'web'],
            ['description' => 'Employee with view-only access to own schedule']
        );

        // Update any users who had old roles to have Employee role
        $employeeRole = Role::where('name', 'Employee')->first();
        if ($employeeRole) {
            DB::table('model_has_roles')
                ->whereIn('role_id', Role::whereIn('name', $rolesToDelete)->pluck('id'))
                ->update(['role_id' => $employeeRole->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Rename Superuser back to Super Admin
        $superuser = Role::where('name', 'Superuser')->first();
        if ($superuser) {
            $superuser->update(['name' => 'Super Admin', 'description' => 'Super Administrator with all permissions']);
        }

        // Recreate old roles
        $roles = [
            ['name' => 'Manager', 'guard_name' => 'web', 'description' => 'Manager with content management permissions'],
            ['name' => 'Editor', 'guard_name' => 'web', 'description' => 'Editor with content editing permissions'],
            ['name' => 'User', 'guard_name' => 'web', 'description' => 'Standard user with basic permissions'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate($role);
        }
    }
};
