<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure new dot-notation permissions exist
        Artisan::call('db:seed', ['--class' => 'RbacSeeder', '--force' => true]);

        // Delete old-format permissions (no dot in name)
        Permission::whereRaw("name NOT LIKE '%.%'")->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
