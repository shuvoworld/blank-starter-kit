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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->enum('holiday_type', ['global', 'regional'])->default('global');
            $table->foreignId('country_id')->nullable()->nullOnDelete()->constrained('countries');
            $table->foreignId('city_id')->nullable()->nullOnDelete()->constrained('cities');
            $table->boolean('is_recurring')->default(false);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['date', 'is_active']);
            $table->index(['holiday_type', 'country_id', 'city_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
