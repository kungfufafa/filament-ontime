<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->integer('late_tolerance_minutes')->default(15);
            $table->boolean('require_photo')->default(false);
            $table->boolean('require_gps')->default(false);
            $table->decimal('geofence_latitude', 10, 7)->nullable();
            $table->decimal('geofence_longitude', 10, 7)->nullable();
            $table->integer('geofence_radius_meters')->nullable();
            $table->integer('annual_leave_quota')->default(12);
            $table->time('work_start_time')->default('08:00:00');
            $table->time('work_end_time')->default('17:00:00');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_policies');
    }
};
