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
        Schema::create('course_user_ratings', function (Blueprint $table) {
            $table->id();
            $table->string('course_id', 20);
            $table->foreign('course_id')->references('course_id')->on('courses');
            $table->string('user_id', 10);
            $table->foreign('user_id')->references('user_id')->on('users');
            $table->integer('rating');
            $table->string('comment', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_user_ratings');
    }
};
