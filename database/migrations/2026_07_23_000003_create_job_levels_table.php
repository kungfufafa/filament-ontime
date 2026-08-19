<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Staff, Manager, Leader, BOD
            $table->integer('level_order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_levels');
    }
};
