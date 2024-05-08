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
        Schema::create('teacher_education_histories', function (Blueprint $table) {
            $table->string('teacher_id', 10)->primary();
            $table->foreign('teacher_id')->references('teacher_id')->on('teachers');
            $table->string('teacher_degree_title', 100);
            $table->string('teacher_university', 255);
            $table->string('teacher_major', 100);
            $table->year('teacher_graduation_year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_education_histories');
    }
};
