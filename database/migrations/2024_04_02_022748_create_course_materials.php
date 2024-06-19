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
            $table->integer('material_bab');
            $table->string('course_id', 20);
            $table->foreign('course_id')->references('course_id')->on('courses')->onDelete('cascade')->onUpdate('cascade');
            $table->string('material_title', 50);
            $table->string('material_sub_title', 50);
            $table->enum('material_file_type', ['pdf', 'video'])->nullable();
            $table->string('material_file', 255)->nullable();
            $table->string('material_description', 255);
            $table->timestamps();
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
