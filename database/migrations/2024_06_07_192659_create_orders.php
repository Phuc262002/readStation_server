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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code');
            $table->date('return_date')->nullable();
            $table->date('receipt_date')->nullable();
            $table->integer('max_extensions')->default(3);
            $table->integer('current_extensions')->default(0);
            $table->json('extension_dates')->nullable();
            $table->date('expired_date')->nullable();
            $table->foreignUuid('user_id');
            $table->enum('payment_method', ['wallet', 'cash']);
            $table->foreignUuid('transaction_id')->nullable();
            $table->enum('payment_shipping', ['library', 'shipper']);
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('user_note')->nullable();
            $table->string('manager_note')->nullable();
            $table->unsignedBigInteger('deposit_fee')->default(0);
            $table->unsignedBigInteger('fine_fee')->default(0);
            $table->unsignedBigInteger('total_fee')->default(0);
            $table->enum('status', ['pending', 'approved', 'wating_take_book', 'hiring', 'increasing', 'wating_return', 'completed', 'canceled', 'out_of_date'])->default('pending');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('wallet_transactions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
