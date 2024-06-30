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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            $table->string('transaction_code')->unique();
            $table->foreignId('loan_order_id');
            $table->enum('portal', ['payos', 'vnpay'])->nullable();
            $table->enum('transaction_type', ['payment', 'refund', 'extend']);
            $table->enum('transaction_method', ['online', 'offline']);
            $table->enum('status', ['pending', 'holding','completed', 'failed', 'canceled'])->default('pending');
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('description')->nullable();
            $table->json('extra_info')->nullable();
            $table->timestamps();

            $table->unique('id');
            $table->foreign('loan_order_id')->references('id')->on('loan_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
