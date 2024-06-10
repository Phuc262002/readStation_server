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
        Schema::create('shelves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bookcase_id');
            $table->string('bookshelf_code');
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('bookcase_id')->references('id')->on('bookcases')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shelves');
    }
};
