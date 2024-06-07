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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('sku_generated')->unique()->nullable();
            $table->unsignedBigInteger('author_id');
            $table->string('title');
            $table->string('original_title');
            $table->text('description_summary');
            $table->enum('status', ['needUpdateDetail','active', 'inactive', 'deleted'])->default('needUpdateDetail');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('shelve_id')->nullable();
            $table->text('description');
            $table->boolean('is_featured')->default(false);
            $table->string('slug')->unique();
            $table->timestamps();
        
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('shelve_id')->references('id')->on('shelves')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
