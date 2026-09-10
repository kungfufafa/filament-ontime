<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('approval_request_steps');
        Schema::dropIfExists('approval_flows');
        Schema::dropIfExists('approvers');
        Schema::dropIfExists('attendance_corrections');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('overtime_requests');
        Schema::dropIfExists('resignations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tables are dropped as part of refocusing to pure attendance system.
    }
};
