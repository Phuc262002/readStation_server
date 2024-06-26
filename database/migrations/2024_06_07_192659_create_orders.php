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
            $table->date('completed_date')->nullable();
            $table->date('receipt_date')->nullable();
            $table->integer('max_extensions')->default(3);
            $table->integer('current_extensions')->default(0);
            $table->json('extension_dates')->nullable();
            $table->date('expired_date')->nullable();
            $table->foreignUuid('user_id');
            $table->enum('payment_method', ['wallet', 'cash']);
            $table->foreignUuid('transaction_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('user_note')->nullable();
            $table->string('manager_note')->nullable();
            $table->unsignedBigInteger('total_deposit_fee')->default(0);
            $table->unsignedBigInteger('total_service_fee')->default(0);
            $table->unsignedBigInteger('total_fine_fee')->default(0);
            $table->unsignedBigInteger('shipping_fee')->default(0);
            $table->unsignedBigInteger('total_all_fee')->default(0);
            $table->unsignedBigInteger('return_fee')->default(0);
            $table->foreignId('shipping_method_id')->nullable();
            $table->unsignedBigInteger('discount')->default(0);
            $table->enum('status', ['pending', 'approved', 'wating_take_book', 'hiring', 'increasing', 'wating_return', 'completed', 'canceled', 'out_of_date'])->default('pending');
            $table->timestamps();

            $table->foreign('shipping_method_id')->references('id')->on('shipping_methods')->onDelete('cascade');
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
