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
        Schema::create('pending_balances', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_id', 10);
            $table->foreign('teacher_id')->references('teacher_id')->on('teachers')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('pb_balance');
            $table->timestamp('pb_release_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_balances');
    }
};
