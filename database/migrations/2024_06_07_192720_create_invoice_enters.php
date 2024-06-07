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
        Schema::create('invoice_enters', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id');
            $table->string('invoice_code');
            $table->string('invoice_name');
            $table->decimal('total', 20, 8);
            $table->string('invoice_description')->nullable();
            $table->unsignedBigInteger('supplier_id');
            $table->enum('status', ['draft','active', 'canceled'])->default('draft');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_enters');
    }
};
