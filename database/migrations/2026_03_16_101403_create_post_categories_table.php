<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        collect(['post-categories.view', 'post-categories.create', 'post-categories.update', 'post-categories.delete'])
            ->each(fn (string $name) => Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['module' => 'post-categories']
            ));
    }

    public function down(): void
    {
        Schema::dropIfExists('post_categories');

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::where('module', 'post-categories')->delete();
    }
};
