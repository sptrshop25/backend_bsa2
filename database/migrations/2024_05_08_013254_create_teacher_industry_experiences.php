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
        Schema::create('teacher_industry_experiences', function (Blueprint $table) {
            $table->string('teacher_id', 10)->primary();
            $table->foreign('teacher_id')->references('teacher_id')->on('teachers');
            $table->string('industry_name');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('position');
            $table->string('description');
            $table->enum('is_still_working', ['yes', 'no'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_industry_experiences');
    }
};
