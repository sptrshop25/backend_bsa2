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
        Schema::create('withdraw_historys', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_id', 10);
            $table->foreign('teacher_id')->references('teacher_id')->on('teachers')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('amount_withdraw');
            $table->integer('fee_withdraw');
            $table->integer('previous_balance');
            $table->integer('current_balance');
            $table->enum('withdraw_method', ['dana', 'gopay', 'ovo', 'bri', 'bni', 'mandiri', 'bca', 'qris']);
            $table->string('account_name', 100);
            $table->string('account_number', 100);
            $table->enum('status', ['process', 'success', 'rejected'])->default('process');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraw_historys');
    }
};
