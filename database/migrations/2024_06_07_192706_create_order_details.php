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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->decimal('service_fee', 20, 8)->default(0);
            $table->unsignedBigInteger('book_details_id');
            $table->date('return_date')->nullable();
            $table->integer('max_extensions')->default(3);
            $table->integer('current_extensions')->default(0);
            $table->json('extension_dates')->nullable();
            $table->date('expired_date')->nullable();
            $table->integer('rate')->default(5);
            $table->date('date_rate')->nullable();
            $table->text('comment')->nullable();
            $table->enum('status_cmt', ['hiring', 'rating_yet', 'active', 'hide', 'canceled'])->default('hiring');
            $table->enum('status_od', ['pending', 'hiring', 'completed', 'canceled', 'out_of_date'])->default('pending');
            $table->decimal('deposit', 20, 8)->default(0);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('book_details_id')->references('id')->on('book_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
