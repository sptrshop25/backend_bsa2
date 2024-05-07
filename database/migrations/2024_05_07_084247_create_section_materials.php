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
        Schema::create('section_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('course_sections');
            $table->enum('material_type', ['video', 'pdf', 'image', 'ppt']);   
            $table->string('material_link', 255); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_materials');
    }
};
