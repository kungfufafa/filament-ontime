<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('is_out_of_bounds')->default(false)->after('is_corrected');
            $table->unsignedTinyInteger('current_step')->nullable()->after('is_out_of_bounds');
            $table->text('rejection_reason')->nullable()->after('current_step');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['is_out_of_bounds', 'current_step', 'rejection_reason']);
        });
    }
};
