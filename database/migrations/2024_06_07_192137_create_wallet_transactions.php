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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            $table->foreignUuid('wallet_id');
            $table->string('reference_id')->unique();
            $table->string('transaction_code')->unique();
            $table->enum('transaction_type', ['deposit', 'withdraw']);
            $table->enum('transaction_method', ['online', 'offline']);
            $table->enum('status', ['pending', 'completed', 'failed', 'canceled'])->default('pending');
            $table->unsignedBigInteger('amount');
            $table->timestamps();

            $table->unique('id');
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
