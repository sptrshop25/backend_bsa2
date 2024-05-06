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
        Schema::create('data_users', function (Blueprint $table) {
            $table->string('user_id', 10)->primary();
            $table->foreign('user_id')->references('user_id')->on('users');
            $table->string('user_name', 100);
            $table->date('user_date_of_birth');
            $table->string('user_address', 100);
            $table->string('user_phone_number', 13);
            $table->string('user_profile_picture');
            $table->string('user_biography', 100);
            $table->enum('user_gender', ['Male', 'Female']);
            $table->string('user_highest_education', 20);
            $table->string('user_occupation',50);
            $table->string('user_interest', 50);
            $table->integer('user_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_users');
    }
};
