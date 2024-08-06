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
        Schema::create('extension_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_id');
            $table->foreignId('loan_order_detail_id');
            $table->unsignedTinyInteger('number_of_days')->default(1);
            $table->date('new_due_date');
            $table->unsignedBigInteger('extension_fee')->default(0);
            $table->timestamps();

            $table->foreign('extension_id')->references('id')->on('extensions')->onDelete('cascade');
            $table->foreign('loan_order_detail_id')->references('id')->on('loan_order_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extension_details');
    }
};
