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
        Schema::create('identities', function (Blueprint $table) {
            $table->string('user_id')->primary();
            $table->foreign('user_id')->references('user_id')->on('users');
            $table->enum('identity_type', ['sim', 'ktp', 'card_student']);
            $table->string('identity_number', 16);
            $table->string('identity_selfie');
            $table->string('identity_front_image');
            $table->enum('identity_status', ['pending', 'accepted', 'rejected']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identities');
    }
};
