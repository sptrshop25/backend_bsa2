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
        Schema::create('users', function (Blueprint $table) {
            $table->string('user_id', 10)->primary();
            $table->string('email', 100);
            $table->string('password');
            $table->string('user_signin_key', 100);
            $table->timestamp('user_access_time')->useCurrent();
            $table->enum('user_role', ['admin', 'user'])->default('user');
            $table->enum('user_status', ['active', 'inactive'])->default('active');
            $table->enum('user_teacher', ['yes', 'no'])->default('no');
            $table->string('verification_token', 100)->nullable();
            $table->string('otp', 10)->nullable();
            $table->enum('user_login_google', ['yes', 'no'])->default('no');
            $table->enum('user_email_verified', ['yes', 'no'])->default('no');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
