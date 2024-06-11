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
        Schema::create('book_details', function (Blueprint $table) {
            $table->id();
            $table->string('sku_origin');
            $table->unsignedBigInteger('book_id');
            $table->string('poster');
            $table->json('images')->nullable();
            $table->string('book_version')->nullable();
            $table->decimal('price', 20, 8)->default(0);
            $table->decimal('hire_percent', 8, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->date('publish_date')->nullable();
            $table->unsignedBigInteger('publishing_company_id')->nullable();
            $table->string('issuing_company')->nullable();
            $table->enum('cardboard', ['soft', 'hard'])->default('soft');
            $table->unsignedInteger('total_page')->nullable();
            $table->string('translator')->nullable();
            $table->string('language')->nullable();
            $table->string('book_size')->nullable();
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active');
            $table->timestamps();

            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('publishing_company_id')->references('id')->on('publishing_companies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_details');
    }
};
