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
        Schema::create('course_transactions', function (Blueprint $table) {
            $table->string('transaction_id', 20)->primary();
            $table->string('user_id', 10);
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('course_id', 20);
            $table->foreign('course_id')->references('course_id')->on('courses')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('transaction_amount')->default(0);
            $table->integer('transaction_fee_merchant')->default(0); 
            $table->integer('transaction_fee_customer')->default(0); 
            $table->integer('transaction_total_fee')->default(0); 
            $table->integer('transaction_total_amount')->default(0); 
            $table->string('transaction_url_checkout')->nullable();
            $table->string('transaction_reference', 50)->nullable(); 
            $table->string('transaction_status', 50)->nullable();
            $table->string('transaction_method', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_transactions');
    }
};
