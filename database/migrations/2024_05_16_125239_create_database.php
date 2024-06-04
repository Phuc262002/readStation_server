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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

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
            $table->string('street')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('address_detail')->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('confirmation_code')->nullable()->default(NULL);
            $table->dateTime('confirmation_code_expired_in')->nullable()->default(NULL);
            $table->string('refresh_token')->nullable();
            $table->string('remember_token')->nullable();
            $table->enum('status', ['active', 'inactive', 'banned', 'delete'])->default('active');
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            $table->foreignUuid('user_id');
            $table->decimal('balance', 20, 8);
            $table->timestamps();

            $table->unique('id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            $table->foreignUuid('wallet_id');
            $table->string('reference_id')->unique();
            $table->string('transaction_code')->unique();
            $table->enum('transaction_type', ['deposit', 'withdraw']);
            $table->enum('transaction_method', ['online', 'offline']);
            $table->enum('status', ['pending', 'completed', 'failed', 'canceled'])->default('pending');
            $table->decimal('amount', 20, 8);
            $table->timestamps();

            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
        });

        Schema::create('verification_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_request_id');
            $table->foreignUuid('user_handle_id');
            $table->enum('verification_card_type', ['student_id_card', 'id_card_number']);
            $table->string('verification_card_image');
            $table->json('verification_card_info');
            $table->date('card_expired');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->date('verification_date')->nullable();
            $table->timestamps();

            $table->foreign('user_request_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_handle_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['book', 'post']);
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('image')->nullable();
            $table->string('slug')->unique();
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active');
            $table->timestamps();
        });

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
            $table->enum('status', ['wating_approve','draft', 'published','hidden', 'deleted'])->default('published');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreignUuid('user_id')->index();
            $table->foreignId('post_id')->index();
            $table->text('content');
            $table->enum('status', ['published','banned', 'hidden', 'delete'])->default('published');
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });

        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('author');
            $table->string('avatar')->nullable();
            $table->date('dob')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('slug')->unique();
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active');
            $table->timestamps();
        });

        Schema::create('publishing_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_company')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active');
            $table->timestamps();
        });

        Schema::create('bookcases', function (Blueprint $table) {
            $table->id();
            $table->string('bookcase_code');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active');
            $table->timestamps();
        });

        Schema::create('shelves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bookcase_id');
            $table->string('bookshelf_code');
            $table->unsignedBigInteger('category_id');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('bookcase_id')->references('id')->on('bookcases')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });

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

        Schema::create('book_details', function (Blueprint $table) {
            $table->id();
            $table->string('sku_origin');
            $table->unsignedBigInteger('book_id');
            $table->string('poster');
            $table->json('images')->nullable();
            $table->string('book_version')->nullable();
            $table->string('price')->nullable();
            $table->decimal('hire_percent', 8, 2)->nullable();
            $table->unsignedInteger('stock')->nullable();
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

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code');
            $table->date('return_date')->nullable();
            $table->integer('max_extensions')->default(3);
            $table->integer('current_extensions')->default(0);
            $table->json('extension_dates')->nullable();
            $table->date('expired_date')->nullable();
            $table->foreignUuid('user_id');
            $table->enum('payment_method', ['wallet', 'cash']);
            $table->foreignUuid('transaction_id')->nullable();
            $table->enum('payment_shipping', ['library', 'shipper']);
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('user_note')->nullable();
            $table->string('manager_note')->nullable();
            $table->decimal('deposit_fee', 20, 8)->default(0);
            $table->decimal('fine_fee', 20, 8)->default(0);
            $table->decimal('total_fee', 20, 8)->default(0);
            $table->enum('status', ['pending', 'approved', 'wating_take_book', 'hiring', 'increasing', 'wating_return', 'completed', 'canceled', 'out_of_date'])->default('pending');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('wallet_transactions')->onDelete('set null');
        });

        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->decimal('service_fee', 20, 8)->default(0);
            $table->unsignedBigInteger('book_details_id');
            $table->date('return_date')->nullable();
            $table->integer('max_extensions')->default(3);
            $table->integer('current_extensions')->default(0);
            $table->json('extension_dates')->nullable();
            $table->date('expired_date')->nullable();
            $table->integer('rate')->default(5);
            $table->date('date_rate')->nullable();
            $table->text('comment')->nullable();
            $table->enum('status_cmt', ['hiring', 'rating_yet', 'active', 'hide', 'canceled'])->default('hiring');
            $table->enum('status_od', ['pending', 'hiring', 'completed', 'canceled', 'out_of_date'])->default('pending');
            $table->decimal('deposit', 20, 8)->default(0);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('book_details_id')->references('id')->on('book_details')->onDelete('cascade');
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('phone');
            $table->string('email');
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active');
            $table->timestamps();
        });

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

        Schema::create('invoice_enter_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_enter_id');
            $table->unsignedBigInteger('book_detail_id');
            $table->decimal('book_price', 20, 8);
            $table->unsignedInteger('book_quantity');
            $table->timestamps();

            $table->foreign('invoice_enter_id')->references('id')->on('invoice_enters')->onDelete('cascade');
            $table->foreign('book_detail_id')->references('id')->on('book_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('verification_requests');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('publishing_companies');
        Schema::dropIfExists('books');
        Schema::dropIfExists('bookcases');
        Schema::dropIfExists('shelves');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('invoice_enters');
        Schema::dropIfExists('invoice_enter_details');
        Schema::dropIfExists('book_details');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_details');

        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
