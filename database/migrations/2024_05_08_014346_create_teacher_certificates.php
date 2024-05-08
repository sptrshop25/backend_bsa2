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
        Schema::create('teacher_certificates', function (Blueprint $table) {
            $table->string('teacher_id', 10)->primary();
            $table->foreign('teacher_id')->references('teacher_id')->on('teachers');
            $table->string('certificate', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_certificates');
    }
};
