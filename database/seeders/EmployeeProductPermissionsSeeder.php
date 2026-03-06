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
        $actions = ['view', 'create', 'update', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(
                    ['name' => "{$module}.{$action}", 'guard_name' => 'web'],
                    ['module' => $module, 'description' => ucfirst($module).' — '.ucfirst($action)]
                );
            }
        }

        $allPermissions = Permission::whereIn('module', $modules)->get();
        $readPermissions = Permission::whereIn('module', $modules)
            ->where('name', 'like', '%.view')
            ->get();
        $crudPermissions = Permission::whereIn('module', $modules)
            ->whereIn('name', collect($modules)->flatMap(fn ($m) => ["{$m}.view", "{$m}.create", "{$m}.update"])->toArray())
            ->get();

        $superAdmin = Role::where('name', 'Superuser')->first();
        $superAdmin?->givePermissionTo($allPermissions);

        $admin = Role::where('name', 'Admin')->first();
        $admin?->givePermissionTo($allPermissions);

        $manager = Role::where('name', 'Manager')->first();
        $manager?->givePermissionTo($crudPermissions);

        $editor = Role::where('name', 'Editor')->first();
        $editor?->givePermissionTo($readPermissions);

        $user = Role::where('name', 'User')->first();
        $user?->givePermissionTo($readPermissions);

        $this->command->info('Employee & Product permissions seeded successfully.');
    }
}
