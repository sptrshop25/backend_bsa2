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
            $table->foreign('teacher_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('teacher_description', 255)->nullable();
            $table->string('teacher_expertise_field', 50);
            $table->string('teacher_instructional_skill', 255);
            $table->string('teacher_link_portfolio', 255)->nullable();
            $table->string('teacher_term_and_condition', 255)->nullable();
            $table->timestamp('teacher_since')->useCurrent();
            $table->string('teacher_link_github', 255)->nullable();
            $table->string('teacher_link_linkedin', 255)->nullable();
            $table->string('teacher_link_youtube', 255)->nullable();
            $table->enum('teacher_status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
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
