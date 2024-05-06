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
        Schema::create('course_materials', function (Blueprint $table) {
            $table->string('material_id', 20)->primary();
            $table->string('course_id', 20);
            $table->foreign('course_id')->references('course_id')->on('courses');
            $table->string('material_title', 20);
            $table->string('material_content', 100);
            $table->string('material_type', 100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_materials');
    }
};
