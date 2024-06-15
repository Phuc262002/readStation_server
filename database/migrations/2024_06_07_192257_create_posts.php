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
            $table->foreignUuid('user_id')->index();
            $table->unsignedBigInteger('category_id');
            $table->string('title');
            $table->text('content');
            $table->text('summary');
            $table->string('image');
            $table->string('slug')->unique();
            $table->integer('view')->default(0);
            $table->enum('status', ['wating_approve', 'approve_canceled', 'draft', 'published','hidden', 'deleted'])->default('published');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
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
