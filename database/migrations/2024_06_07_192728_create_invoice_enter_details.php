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
        Schema::create('invoice_enter_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_enter_id');
            $table->unsignedBigInteger('book_detail_id');
            $table->decimal('book_price', 20, 8);
            $table->unsignedInteger('book_quantity');
            $table->timestamps();

            $table->foreign('invoice_enter_id')->references('id')->on('invoice_enters')->onDelete('cascade');
            $table->foreign('book_detail_id')->references('id')->on('book_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_enter_details');
    }
};
