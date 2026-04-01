<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('to')->nullable()->default(null);
            $table->text('cc')->nullable()->default(null);
            $table->text('bcc')->nullable()->default(null);
            $table->string('subject',255)->nullable()->default(null);
            $table->text('html')->nullable()->default(null);
            $table->string('status',100)->nullable()->default(null);
            $table->unsignedInteger('attempts')->nullable()->default(null);
            $table->dateTime('last_attempted_at')->nullable()->default(null);
            $table->dateTime('successfully_delivered_at')->nullable()->default(null);
            $table->unsignedBigInteger('to_user_id')->nullable()->default(null);
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        collect(['emails.view', 'emails.create', 'emails.update', 'emails.delete'])
            ->each(fn (string $name) => Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['module' => 'emails']
            ));
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::where('module', 'emails')->delete();
    }
};
