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
        Schema::create('teacher_balances', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_id', 10);
            $table->foreign('teacher_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('balance');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_balances');
    }
};
