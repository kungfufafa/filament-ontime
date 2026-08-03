<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('date', 'attendances_date_idx');
            $table->index('status', 'attendances_status_idx');
            $table->index('is_out_of_bounds', 'attendances_is_out_of_bounds_idx');
            $table->index(['date', 'status'], 'attendances_date_status_idx');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index('status', 'leave_requests_status_idx');
            $table->index('start_date', 'leave_requests_start_date_idx');
            $table->index('end_date', 'leave_requests_end_date_idx');
            $table->index(['employee_id', 'status'], 'leave_requests_emp_status_idx');
            $table->index(['intern_id', 'status'], 'leave_requests_intern_status_idx');
            $table->index(['freelancer_id', 'status'], 'leave_requests_free_status_idx');
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->index('status', 'overtime_requests_status_idx');
            $table->index('date', 'overtime_requests_date_idx');
            $table->index(['employee_id', 'status'], 'overtime_requests_emp_status_idx');
            $table->index(['intern_id', 'status'], 'overtime_requests_intern_status_idx');
            $table->index(['freelancer_id', 'status'], 'overtime_requests_free_status_idx');
        });

        Schema::table('attendance_corrections', function (Blueprint $table) {
            $table->index('status', 'attendance_corrections_status_idx');
            $table->index('date', 'attendance_corrections_date_idx');
            $table->index(['employee_id', 'status'], 'attendance_corrections_emp_status_idx');
            $table->index(['intern_id', 'status'], 'attendance_corrections_intern_status_idx');
            $table->index(['freelancer_id', 'status'], 'attendance_corrections_free_status_idx');
        });

        Schema::table('resignations', function (Blueprint $table) {
            $table->index('status', 'resignations_status_idx');
            $table->index(['employee_id', 'status'], 'resignations_emp_status_idx');
        });

        Schema::table('approval_request_steps', function (Blueprint $table) {
            $table->index('status', 'approval_request_steps_status_idx');
            $table->index(['approvable_type', 'approvable_id', 'step_order'], 'approval_steps_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_date_idx');
            $table->dropIndex('attendances_status_idx');
            $table->dropIndex('attendances_is_out_of_bounds_idx');
            $table->dropIndex('attendances_date_status_idx');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('leave_requests_status_idx');
            $table->dropIndex('leave_requests_start_date_idx');
            $table->dropIndex('leave_requests_end_date_idx');
            $table->dropIndex('leave_requests_emp_status_idx');
            $table->dropIndex('leave_requests_intern_status_idx');
            $table->dropIndex('leave_requests_free_status_idx');
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->dropIndex('overtime_requests_status_idx');
            $table->dropIndex('overtime_requests_date_idx');
            $table->dropIndex('overtime_requests_emp_status_idx');
            $table->dropIndex('overtime_requests_intern_status_idx');
            $table->dropIndex('overtime_requests_free_status_idx');
        });

        Schema::table('attendance_corrections', function (Blueprint $table) {
            $table->dropIndex('attendance_corrections_status_idx');
            $table->dropIndex('attendance_corrections_date_idx');
            $table->dropIndex('attendance_corrections_emp_status_idx');
            $table->dropIndex('attendance_corrections_intern_status_idx');
            $table->dropIndex('attendance_corrections_free_status_idx');
        });

        Schema::table('resignations', function (Blueprint $table) {
            $table->dropIndex('resignations_status_idx');
            $table->dropIndex('resignations_emp_status_idx');
        });

        Schema::table('approval_request_steps', function (Blueprint $table) {
            $table->dropIndex('approval_request_steps_status_idx');
            $table->dropIndex('approval_steps_lookup_idx');
        });
    }
};
