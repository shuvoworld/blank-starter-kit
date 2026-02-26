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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->unsignedInteger('max_days_per_year')->nullable();
            $table->unsignedInteger('max_days_per_month')->nullable();
            $table->boolean('carry_forward')->default(false);
            $table->unsignedInteger('carry_forward_limit')->nullable();
            $table->unsignedInteger('carry_forward_expiry_days')->nullable();
            $table->boolean('requires_document')->default(false);
            $table->unsignedInteger('min_days_before_request')->nullable();
            $table->unsignedInteger('max_consecutive_days')->nullable();
            $table->boolean('is_gender_specific')->default(false);
            $table->enum('applicable_gender', ['male', 'female', 'other'])->nullable();
            $table->boolean('is_paid_pro_rata')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
