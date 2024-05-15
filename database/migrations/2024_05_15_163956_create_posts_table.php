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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('thumbnail');
            $table->string('title');
            $table->string('summary');
            $table->text('content');
            $table->unsignedBigInteger('category_id');
            $table->string('slug')->unique();
            $table->enum('status', ['draft', 'published','hidden', 'deleted'])->default('published');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
