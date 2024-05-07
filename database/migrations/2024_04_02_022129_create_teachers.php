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
        Schema::create('teachers', function (Blueprint $table) {
            $table->string('teacher_id', 30)->primary()->unique();
            $table->foreign('teacher_id')->references('user_id')->on('users');
            $table->string('teacher_academic_degree', 100);
            $table->string('teacher_university', 255);
            $table->string('teacher_major', 100);
            $table->string('teacher_education_experience', 255);
            $table->string('teacher_industries_experience', 255);
            $table->string('teacher_expertise_field', 50);
            $table->string('teacher_instructional_skill', 255);
            $table->string('teacher_link_portfolio', 255)->nullable();
            $table->string('teacher_certificate', 255)->nullable();
            $table->string('teacher_term_and_condition', 255)->nullable();
            $table->string('teacher_faq', 255)->nullable();
            $table->integer('teacher_pending_balance')->nullable();
            $table->integer('teacher_available_balance')->nullable();
            $table->timestamp('teacher_since')->useCurrent();
            $table->enum('teacher_status', ['Active', 'Inactive'])->default('Active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher');
    }
};