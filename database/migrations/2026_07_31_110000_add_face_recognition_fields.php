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
        // Add master face fields to worker tables
        Schema::table('employees', function (Blueprint $table) {
            $table->string('master_face_photo')->nullable()->after('status');
            $table->timestamp('master_face_verified_at')->nullable()->after('master_face_photo');
        });

        Schema::table('interns', function (Blueprint $table) {
            $table->string('master_face_photo')->nullable()->after('status');
            $table->timestamp('master_face_verified_at')->nullable()->after('master_face_photo');
        });

        Schema::table('freelancers', function (Blueprint $table) {
            $table->string('master_face_photo')->nullable()->after('status');
            $table->timestamp('master_face_verified_at')->nullable()->after('master_face_photo');
        });

        // Add face recognition settings to company_policies table
        Schema::table('company_policies', function (Blueprint $table) {
            $table->boolean('require_face_recognition')->default(false)->after('require_gps');
            $table->unsignedTinyInteger('face_match_threshold')->default(60)->after('require_face_recognition');
            $table->string('face_fail_action')->default('reject')->after('face_match_threshold');
        });

        // Add face verification results to attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('is_face_verified')->nullable()->after('is_out_of_bounds');
            $table->unsignedTinyInteger('face_match_score')->nullable()->after('is_face_verified');
            $table->string('face_verification_notes')->nullable()->after('face_match_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['master_face_photo', 'master_face_verified_at']);
        });

        Schema::table('interns', function (Blueprint $table) {
            $table->dropColumn(['master_face_photo', 'master_face_verified_at']);
        });

        Schema::table('freelancers', function (Blueprint $table) {
            $table->dropColumn(['master_face_photo', 'master_face_verified_at']);
        });

        Schema::table('company_policies', function (Blueprint $table) {
            $table->dropColumn(['require_face_recognition', 'face_match_threshold', 'face_fail_action']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['is_face_verified', 'face_match_score', 'face_verification_notes']);
        });
    }
};
