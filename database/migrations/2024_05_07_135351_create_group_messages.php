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
        Schema::create('group_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade')->onUpdate('cascade');
            $table->string('sender_id', 10);
            $table->foreign('sender_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('receiver_id', 10);
            $table->foreign('receiver_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('message', 255);
            $table->string('attachment', 255)->nullable();
            $table->integer('reply_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_messages');
    }
};
