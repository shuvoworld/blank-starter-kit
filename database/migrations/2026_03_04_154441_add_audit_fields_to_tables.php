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
        // List of tables to add audit fields
        $tables = [
            'departments',
            'designations',
            'employees',
            'products',
            'leave_types',
            'holidays',
            'landing_page_sections',
            'leave_balances',
            'leave_requests',
            'users',
        ];

        foreach ($tables as $table) {
            // Add soft deletes, created_by, updated_by, deleted_by
            Schema::table($table, function (Blueprint $tableObj) use ($table) {
                // Add soft deletes if not exists
                if (! Schema::hasColumn($table, 'deleted_at')) {
                    $tableObj->softDeletes();
                }

                // Add deleted_by if not exists
                if (! Schema::hasColumn($table, 'deleted_by')) {
                    $tableObj->foreignId('deleted_by')->nullable()->after('deleted_at')->constrained('users')->nullOnDelete();
                }
            });

            // Add created_by and updated_by if not exists
            Schema::table($table, function (Blueprint $tableObj) use ($table) {
                if (! Schema::hasColumn($table, 'created_by')) {
                    $tableObj->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn($table, 'updated_by')) {
                    $tableObj->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // List of tables to remove audit fields
        $tables = [
            'departments',
            'designations',
            'employees',
            'products',
            'leave_types',
            'holidays',
            'landing_page_sections',
            'leave_balances',
            'leave_requests',
            'users',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableObj) {
                // Drop foreign keys and columns
                if (Schema::hasColumn($table, 'deleted_by')) {
                    $tableObj->dropForeign(['deleted_by']);
                    $tableObj->dropColumn('deleted_by');
                }
                if (Schema::hasColumn($table, 'updated_by')) {
                    $tableObj->dropForeign(['updated_by']);
                    $tableObj->dropColumn('updated_by');
                }
                if (Schema::hasColumn($table, 'created_by')) {
                    $tableObj->dropForeign(['created_by']);
                    $tableObj->dropColumn('created_by');
                }
            });
        }
    }
};
