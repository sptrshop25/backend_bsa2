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
        Schema::create('courses', function (Blueprint $table) {
            $table->string('course_id', 20)->primary();
            $table->string('teacher_id', 20);
            $table->foreign('teacher_id')->references('teacher_id')->on('teachers');
            $table->string('course_title', 50);
            $table->foreignId('course_category_id')->constrained('course_sub_categories');
            $table->string('course_description', 100);
            $table->integer('course_price');
            $table->integer('course_rating')->nullable();
            $table->enum('course_level', ['beginner', 'intermediate', 'advanced']);
            $table->enum('course_is_free', ['yes', 'no'])->default('no');
            $table->integer('course_duration')->nullable();
            $table->string('course_image', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
