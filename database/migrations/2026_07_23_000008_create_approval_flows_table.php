<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->enum('request_type', ['leave', 'overtime', 'correction']);
            $table->integer('step_number')->default(1);
            $table->string('name'); // e.g. Direct Manager / HR Verification / Final Approval
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_flows');
    }
};
