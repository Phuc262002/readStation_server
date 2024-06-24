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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            $table->unsignedBigInteger('role_id')->default(1);
            $table->string('avatar')->nullable();
            $table->string('fullname');
            $table->string('job')->nullable();
            $table->string('story')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('dob')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('google_id')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('user_verified_at')->nullable();
            $table->boolean('has_wallet')->default(false);
            $table->json('citizen_identity_card')->nullable();
            $table->json('student_id_card')->nullable();
            $table->string('province_id')->nullable();
            $table->string('district_id')->nullable();
            $table->string('ward_id')->nullable();
            $table->string('street')->nullable();
            $table->string('address_detail')->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('confirmation_code')->nullable()->default(NULL);
            $table->dateTime('confirmation_code_expired_in')->nullable()->default(NULL);
            $table->string('refresh_token')->nullable();
            $table->string('remember_token')->nullable();
            $table->enum('status', ['active', 'inactive', 'banned', 'deleted'])->default('active');
            $table->timestamps();

            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
            $table->foreign('ward_id')->references('id')->on('wards')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
