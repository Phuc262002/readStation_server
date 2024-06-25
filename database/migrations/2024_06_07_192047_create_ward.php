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
        Schema::create('wards', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('DistrictID');
            $table->string('WardName');
            $table->json('NameExtension');
            $table->timestamps();

            $table->foreign('DistrictID')->references('id')->on('districts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wards');
    }
};