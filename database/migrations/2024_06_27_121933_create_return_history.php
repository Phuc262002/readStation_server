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
        Schema::create('return_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_order_details_id');
            $table->datetime('return_date')->nullable();
            $table->string('condition')->nullable();
            $table->unsignedBigInteger('fine_amount')->default(0);
            $table->foreignUuid('processed_by')->nullable();
            $table->enum('return_method', ['pickup', 'library'])->default('library');
            $table->json('pickup_info')->nullable();
            $table->foreignId('shipping_method_id')->nullable();
            $table->unsignedBigInteger('return_shipping_fee')->default(0);
            $table->datetime('pickup_date')->nullable();
            $table->datetime('received_at_library_date')->nullable();
            $table->enum('status', ['pending', 'approved', 'preparing_shipment', 'in_transit', 'completed', 'lost'])->default('pending');
            $table->timestamps();

            $table->foreign('shipping_method_id')->references('id')->on('shipping_methods')->onDelete('set null');
            $table->foreign('loan_order_details_id')->references('id')->on('loan_order_details')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_histories');
    }
};
