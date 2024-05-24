<?php

use App\Http\Controllers\Api\Admin\AuthorController as AdminAuthorController;
use App\Http\Controllers\Api\Admin\BookController as AdminBookController;
use App\Http\Controllers\Api\Admin\BookDetailController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\PostController as AdminPostController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\Client\AccountController;
use App\Http\Controllers\Api\Client\OrderController;
use App\Http\Controllers\Api\Public\AuthorController;
use App\Http\Controllers\Api\Public\BookController;
use App\Http\Controllers\Api\Public\CategoryController;
use App\Http\Controllers\Api\Public\HomeController as PublicHomeController;
use App\Http\Controllers\Api\Public\PostController as PublicPostController;
use Illuminate\Support\Facades\Route;



Route::group([
    "prefix" => "v1"
], function () {
    Route::group([
        "prefix" => "auth"
    ], function () {
        Route::post('/register', [AuthController::class, 'register']);

        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/google', [AuthController::class, 'loginWithGoogle']);

        Route::post('/refresh', [AuthController::class, 'refresh']);

        Route::post('/send-reset-password', [PasswordController::class, 'sendRequestForgotPassword'])->name('password.reset');
        Route::post('/reset-password', [PasswordController::class, 'changePassWordReset']);

        Route::post('/verify-email', [VerifyEmailController::class, 'verifyEmail']);
        Route::post('/resend-otp', [VerifyEmailController::class, 'reRegister']);


        Route::group(["middleware" => ["auth:api"]], function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            
            Route::post('/change-password', [PasswordController::class, 'changePassWord']);
        });
    });

    Route::group([
        "prefix" => "account",
        "middleware" => ["auth:api"]
    ], function () {
        Route::get('/get-profile', [AccountController::class, 'userProfile']);
        Route::put('/update-profile', [AccountController::class, 'updateProfile']);
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/get-one/{order}', [OrderController::class, 'show']);
    });

    Route::group([
        "prefix" => "home"
    ], function () {
        Route::get('/get-feautured-author', [PublicHomeController::class, 'getFeaturedAuthor']);
        Route::get('/get-feautured-book', [PublicHomeController::class, 'getFeaturedBook']);
    });

    Route::group([
        "prefix" => "categories"
    ], function () {
        Route::get('/', [CategoryController::class, 'index']);

        Route::group(["middleware" => ["auth:api"]], function () {
            Route::get('/admin/get-all', [AdminCategoryController::class, 'getAllCategory']);
            Route::get('/get-one/{id}', [AdminCategoryController::class, 'show']);
            Route::post('/create', [AdminCategoryController::class, 'store']);
            Route::put('/update/{id}', [AdminCategoryController::class, 'update']);
            Route::delete('/delete/{id}', [AdminCategoryController::class, 'destroy']);
        });
    });

    Route::group([
        "prefix" => "books"
    ], function () {
        Route::get('/', [BookController::class, 'index']);
        Route::get('/get-one/{book}', [BookController::class, 'show']);

        Route::group(["middleware" => ["auth:api"]], function () {
            Route::get('/admin/get-all', [AdminBookController::class, 'getAllBook']);
            Route::post('/create', [AdminBookController::class, 'store']);
            Route::put('/update/{id}', [AdminBookController::class, 'update']);
            Route::delete('/delete/{id}', [AdminBookController::class, 'destroy']);
        });
    });

    Route::group([
        "prefix" => "posts"
    ], function () {
        Route::get('/', [PublicPostController::class, 'index']);
        Route::get('/get-one/{post}', [PublicPostController::class, 'show']);

        Route::group(["middleware" => ["auth:api"]], function () {
            Route::post('/create', [AdminPostController::class, 'store']);
            Route::put('/update/{id}', [AdminPostController::class, 'update']);
            Route::delete('/delete/{id}', [AdminPostController::class, 'destroy']);
        });
    });

    Route::group([
        "prefix" => "book-details",
        "middleware" => ["auth:api"]
    ], function () {
        Route::get('/get-one/{id}', [BookDetailController::class, 'show']);
        Route::post('/create', [BookDetailController::class, 'store']);
        Route::put('/update/{id}', [BookDetailController::class, 'update']);
        Route::delete('/delete/{id}', [BookDetailController::class, 'destroy']);
    });

    Route::group([
        "prefix" => "authors"
    ], function () {
        Route::get('/', [AuthorController::class, 'index']);

        Route::group(["middleware" => ["auth:api"]], function () {
            Route::get('/admin/get-all', [AdminAuthorController::class, 'getAllAuthor']);
            Route::get('/get-one/{id}', [AdminAuthorController::class, 'show']);
            Route::post('/create', [AdminAuthorController::class, 'store']);
            Route::put('/update/{id}', [AdminAuthorController::class, 'update']);
            Route::delete('/delete/{id}', [AdminAuthorController::class, 'destroy']);
        });
    });
});
