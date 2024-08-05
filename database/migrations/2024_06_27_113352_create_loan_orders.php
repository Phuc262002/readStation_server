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
        Schema::create('loan_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code');

            $table->foreignUuid('user_id');
            $table->enum('payment_method', ['online', 'cash']);
            $table->string('user_note')->nullable();
            $table->string('reason_cancel')->nullable();

            $table->unsignedTinyInteger('max_extensions')->default(3);
            $table->unsignedTinyInteger('current_extensions')->default(0);

            $table->unsignedTinyInteger('number_of_days')->default(1);
            $table->datetime ('loan_date')->nullable();
            $table->datetime ('original_due_date')->nullable();
            $table->datetime ('current_due_date')->nullable();
            $table->datetime ('completed_date')->nullable();

            $table->unsignedBigInteger('total_deposit_fee')->default(0);
            $table->unsignedBigInteger('total_service_fee')->default(0);
            $table->unsignedBigInteger('total_fine_fee')->default(0);
            $table->unsignedBigInteger('total_shipping_fee')->default(0);
            $table->unsignedBigInteger('total_all_fee')->default(0);
            $table->unsignedBigInteger('total_return_fee')->default(0);

            $table->enum('delivery_method', ['pickup', 'shipper'])->default('pickup');
            $table->json('delivery_info')->nullable();
            $table->foreignId('shipping_method_id')->nullable();
            $table->dateTime('pickup_date')->nullable();
            $table->dateTime('delivered_date')->nullable();

            $table->enum('status', ['wating_payment', 'pending', 'approved', 'ready_for_pickup', 'preparing_shipment', 'in_transit', 'active', 'extended', 'returning', 'completed', 'canceled', 'overdue'])->default('pending');
            $table->timestamps();

            $table->foreign('shipping_method_id')->references('id')->on('shipping_methods')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_orders');
    }
};
