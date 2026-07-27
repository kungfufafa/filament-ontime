<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Make employee_id nullable to support intern/freelancer attendances
            $table->foreignId('intern_id')->nullable()->after('employee_id')->constrained('interns')->cascadeOnDelete();
            $table->foreignId('freelancer_id')->nullable()->after('intern_id')->constrained('freelancers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['intern_id']);
            $table->dropForeign(['freelancer_id']);
            $table->dropColumn(['intern_id', 'freelancer_id']);
        });
    }
};
