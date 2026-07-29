<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->foreignId('intern_id')->nullable()->after('employee_id')->constrained('interns')->cascadeOnDelete();
            $table->foreignId('freelancer_id')->nullable()->after('intern_id')->constrained('freelancers')->cascadeOnDelete();

            // Make employee_id nullable since intern/freelancer can also submit leave requests
            $table->foreignId('employee_id')->nullable()->change();
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->foreignId('intern_id')->nullable()->after('employee_id')->constrained('interns')->cascadeOnDelete();
            $table->foreignId('freelancer_id')->nullable()->after('intern_id')->constrained('freelancers')->cascadeOnDelete();

            // Make employee_id nullable since intern/freelancer can also submit overtime requests
            $table->foreignId('employee_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['intern_id']);
            $table->dropForeign(['freelancer_id']);
            $table->dropColumn(['intern_id', 'freelancer_id']);
            $table->foreignId('employee_id')->nullable(false)->change();
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->dropForeign(['intern_id']);
            $table->dropForeign(['freelancer_id']);
            $table->dropColumn(['intern_id', 'freelancer_id']);
            $table->foreignId('employee_id')->nullable(false)->change();
        });
    }
};
