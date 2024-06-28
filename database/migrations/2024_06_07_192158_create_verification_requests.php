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
        Schema::create('verification_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_request_id');
            $table->foreignUuid('user_handle_id')->nullable();
            $table->enum('verification_card_type', ['student_card', 'citizen_card']);
            $table->json('verification_card_image');
            $table->json('verification_card_info');
            $table->string('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->dateTime('verification_date')->nullable();
            $table->timestamps();

            $table->foreign('user_request_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_handle_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_requests');
    }
};
