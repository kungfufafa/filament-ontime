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
        Schema::table('approvers', function (Blueprint $table) {
            $table->unique(['user_id', 'company_id', 'division_id', 'level'], 'unique_approver_mapping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approvers', function (Blueprint $table) {
            $table->dropUnique('unique_approver_mapping');
        });
    }
};
