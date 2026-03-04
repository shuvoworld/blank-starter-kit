<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        collect([
            'view any employee categories',
            'create employee categories',
            'update employee categories',
            'delete employee categories',
        ])->each(fn (string $name) => Permission::firstOrCreate(['name' => $name]));
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_categories');

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::whereIn('name', [
            'view any employee categories',
            'create employee categories',
            'update employee categories',
            'delete employee categories',
        ])->delete();
    }
};
