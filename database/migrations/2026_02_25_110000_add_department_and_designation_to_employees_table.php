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
            // Add foreign key columns if they don't exist
            if (!Schema::hasColumn('employees', 'department_id')) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('department')
                    ->nullOnDelete()
                    ->constrained('departments');
            }

            if (!Schema::hasColumn('employees', 'designation_id')) {
                $table->foreignId('designation_id')
                    ->nullable()
                    ->after('position')
                    ->nullOnDelete()
                    ->constrained('designations');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'designation_id')) {
                $table->dropForeign(['designation_id']);
                $table->dropColumn('designation_id');
            }

            if (Schema::hasColumn('employees', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
        });
    }
};
