<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ActivityLogPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate(
            ['name' => 'activity-log.view', 'guard_name' => 'web'],
            ['module' => 'activity-log', 'description' => 'Activity Log — View']
        );

        foreach (['Superuser', 'Admin'] as $roleName) {
            Role::where('name', $roleName)->first()?->givePermissionTo($permission);
        }

        $this->command->info('Activity log permission seeded successfully.');
    }
}
