<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class EmployeeProductPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = ['employees', 'products'];
        $actions = ['view any', 'view', 'create', 'update', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(
                    ['name' => "{$action} {$module}", 'guard_name' => 'web'],
                    ['module' => $module, 'description' => ucfirst($action).' '.ucfirst($module)]
                );
            }
        }

        $allPermissions = Permission::whereIn('module', $modules)->get();
        $readPermissions = Permission::whereIn('module', $modules)
            ->whereIn('name', collect($modules)->flatMap(fn ($m) => ["view any {$m}", "view {$m}"])->toArray())
            ->get();
        $crudPermissions = Permission::whereIn('module', $modules)
            ->whereIn('name', collect($modules)->flatMap(fn ($m) => ["view any {$m}", "view {$m}", "create {$m}", "update {$m}"])->toArray())
            ->get();

        $superAdmin = Role::where('name', 'Superuser')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($allPermissions);
        }

        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $admin->givePermissionTo($allPermissions);
        }

        $manager = Role::where('name', 'Manager')->first();
        if ($manager) {
            $manager->givePermissionTo($crudPermissions);
        }

        $editor = Role::where('name', 'Editor')->first();
        if ($editor) {
            $editor->givePermissionTo($readPermissions);
        }

        $user = Role::where('name', 'User')->first();
        if ($user) {
            $user->givePermissionTo($readPermissions);
        }

        $this->command->info('Employee & Product permissions seeded successfully.');
    }
}
