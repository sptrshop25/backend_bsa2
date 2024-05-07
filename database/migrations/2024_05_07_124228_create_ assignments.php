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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('course_id');
            $table->foreign('course_id')->references('course_id')->on('courses');
            $table->string('assignment_title', 100);
            $table->string('assignment_description', 255);
            $table->enum('assignment_type', ['essai', 'pg']);
            $table->enum('assignment_task_type', ['quiz', 'assignment', 'exam']);
            $table->timestamp('assignment_proccessing_time')->nullable();
            $table->timestamp('assignment_deadline')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
