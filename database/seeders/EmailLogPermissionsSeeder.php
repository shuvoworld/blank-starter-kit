<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class EmailLogPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate(
            ['name' => 'email-logs.view', 'guard_name' => 'web'],
            ['module' => 'email-logs', 'description' => 'Email Logs — View']
        );

        foreach (['Superuser', 'Admin'] as $roleName) {
            Role::where('name', $roleName)->first()?->givePermissionTo($permission);
        }

        $this->command->info('Email log permission seeded successfully.');
    }
}
