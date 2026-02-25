<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->integer('year')->index();
            $table->unsignedInteger('total_entitlement')->default(0);
            $table->unsignedInteger('taken_days')->default(0);
            $table->unsignedInteger('remaining_days')->default(0);
            $table->unsignedInteger('pending_days')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'leave_type_id', 'year'], 'unique_user_leave_type_year');
            $table->index(['user_id', 'year']);
            $table->index(['leave_type_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
