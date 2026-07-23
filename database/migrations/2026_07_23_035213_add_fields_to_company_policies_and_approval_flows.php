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
        Schema::table('company_policies', function (Blueprint $table) {
            $table->integer('default_approval_stages')->default(1)->after('annual_leave_quota');
        });

        Schema::table('approval_flows', function (Blueprint $table) {
            $table->integer('step_order')->default(1)->after('request_type');
            $table->string('approver_type')->default('role')->after('name');
            $table->string('approver_role')->nullable()->after('approver_type');
            $table->foreignId('user_id')->nullable()->after('approver_role')->constrained('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_policies', function (Blueprint $table) {
            $table->dropColumn('default_approval_stages');
        });

        Schema::table('approval_flows', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['step_order', 'approver_type', 'approver_role', 'user_id']);
        });
    }
};
