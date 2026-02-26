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
        Schema::table('employees', function (Blueprint $table) {
            // Add country_id if it doesn't exist
            if (! Schema::hasColumn('employees', 'country_id')) {
                $table->foreignId('country_id')
                    ->nullable()
                    ->after('status')
                    ->nullOnDelete()
                    ->constrained('countries');
            }

            // Add city_id (state) if it doesn't exist
            if (! Schema::hasColumn('employees', 'city_id')) {
                $table->foreignId('city_id')
                    ->nullable()
                    ->after('country_id')
                    ->nullOnDelete()
                    ->constrained('cities');
            }

            // Add area_id if it doesn't exist
            if (! Schema::hasColumn('employees', 'area_id')) {
                $table->foreignId('area_id')
                    ->nullable()
                    ->after('city_id')
                    ->nullOnDelete()
                    ->constrained('areas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'area_id')) {
                $table->dropForeign(['area_id']);
                $table->dropColumn('area_id');
            }

            if (Schema::hasColumn('employees', 'city_id')) {
                $table->dropForeign(['city_id']);
                $table->dropColumn('city_id');
            }

            if (Schema::hasColumn('employees', 'country_id')) {
                $table->dropForeign(['country_id']);
                $table->dropColumn('country_id');
            }
        });
    }
};
