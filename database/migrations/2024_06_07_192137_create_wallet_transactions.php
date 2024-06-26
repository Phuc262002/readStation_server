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
            $table->enum('transaction_type', ['deposit', 'withdraw', 'payment', 'refund']);
            $table->enum('transaction_method', ['online', 'offline', 'wallet']);
            $table->enum('status', ['pending', 'holding','completed', 'failed', 'canceled'])->default('pending');
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('description')->nullable();
            $table->json('verification_secret_code')->nullable();
            $table->json('bank_info')->nullable();
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
