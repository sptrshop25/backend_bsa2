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
        Schema::create('courses_registrations', function (Blueprint $table) {
            $table->string('cr_id', 20)->primary();
            $table->string('student_id', 20);
            $table->foreign('student_id')->references('user_id')->on('users');
            $table->string('course_id', 20);
            $table->foreign('course_id')->references('course_id')->on('courses');
            $table->timestamp('cr_registered_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses_registrations');
    }
};
