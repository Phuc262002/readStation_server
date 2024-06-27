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
        Schema::create('loan_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_order_id');
            $table->unsignedBigInteger('book_details_id');
            $table->unsignedBigInteger('service_fee')->default(0);
            $table->unsignedBigInteger('deposit_fee')->default(0);
            $table->unsignedBigInteger('fine_amount')->default(0);
            $table->datetime('original_due_date')->nullable();
            $table->datetime('current_due_date')->nullable();
            $table->datetime('return_date')->nullable();
            $table->enum('actual_return_condition', ['excellent', 'good', 'fair', 'poor', 'damaged', 'lost'])->nullable();
            $table->enum('status', ['pending', 'active', 'extended', 'returning', 'completed', 'canceled', 'overdue'])->default('pending');
            $table->timestamps();

            $table->foreign('loan_order_id')->references('id')->on('loan_orders')->onDelete('cascade');
            $table->foreign('book_details_id')->references('id')->on('book_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_order_details');
    }
};
