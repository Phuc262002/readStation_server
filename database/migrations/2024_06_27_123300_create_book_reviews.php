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
        Schema::create('book_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_order_details_id');
            $table->foreignId('book_details_id');
            $table->foreignUuid('user_id');
            $table->text('review_text');
            $table->unsignedTinyInteger('rating');
            $table->datetime('review_date');
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active');
            $table->timestamps();

            $table->foreign('loan_order_details_id')->references('id')->on('loan_order_details')->onDelete('cascade');
            $table->foreign('book_details_id')->references('id')->on('book_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_reviews');
    }
};
