<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Partial unique indexes: only MySQL/MariaDB supports WHERE clause in CREATE UNIQUE INDEX.
            // For SQLite (dev), we rely on application-level validation.
            if (DB::getDriverName() !== 'sqlite') {
                // These are created as raw statements because Laravel Blueprint
                // does not support partial/conditional unique indexes natively.
            }

            // For all drivers: standard composite unique index per worker type.
            // NULLs are not considered equal in unique indexes (SQL standard),
            // so having NULL in employee_id won't conflict across intern rows.
            $table->unique(['employee_id', 'date'], 'attendances_employee_date_unique');
            $table->unique(['intern_id', 'date'], 'attendances_intern_date_unique');
            $table->unique(['freelancer_id', 'date'], 'attendances_freelancer_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_employee_date_unique');
            $table->dropUnique('attendances_intern_date_unique');
            $table->dropUnique('attendances_freelancer_date_unique');
        });
    }
};
