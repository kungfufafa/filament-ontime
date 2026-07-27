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
        Schema::table('attendance_corrections', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->change();
            $table->foreignId('intern_id')->nullable()->after('employee_id')->constrained('interns')->cascadeOnDelete();
            $table->foreignId('freelancer_id')->nullable()->after('intern_id')->constrained('freelancers')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_corrections', function (Blueprint $table) {
            $table->dropForeign(['intern_id']);
            $table->dropForeign(['freelancer_id']);
            $table->dropColumn(['intern_id', 'freelancer_id']);
            $table->foreignId('employee_id')->nullable(false)->change();
        });
    }
};
