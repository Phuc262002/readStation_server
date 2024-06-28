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
        Schema::create('wallet_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('wallet_id');
            $table->unsignedBigInteger('previous_balance')->nullable();
            $table->unsignedBigInteger('new_balance')->nullable();
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->string('action');
            $table->string('reason')->nullable();
            $table->timestamps();
            
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_history');
    }
};
