<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BookDetailController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::group([
    "prefix" => "v1"
], function () {
    Route::group([
        "prefix" => "auth"
    ], function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/google', [AuthController::class, 'loginWithGoogle']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/refresh', [AuthController::class, 'refresh']);

        Route::group(["middleware" => ["auth:api"]], function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/profile', [AuthController::class, 'userProfile']);
            Route::put('/update-profile', [AuthController::class, 'updateProfile']);
            Route::post('/change-password', [AuthController::class, 'changePassWord']);
        });
    });

    Route::group([
        "prefix" => "home"
    ], function () {
        Route::get('/get-feautured-author', [HomeController::class, 'getFeaturedAuthor']);
        Route::get('/get-feautured-book', [HomeController::class, 'getFeaturedBook']);
    });

    Route::group([
        "prefix" => "categories"
    ], function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::post('/create', [CategoryController::class, 'store']);
        Route::put('/update/{id}', [CategoryController::class, 'update']);
        Route::delete('/delete/{id}', [CategoryController::class, 'destroy']);
    });

    Route::group([
        "prefix" => "books"
    ], function () {
        Route::get('/', [BookController::class, 'index']);
        Route::get('/{id}', [BookController::class, 'show']);
        Route::post('/create', [BookController::class, 'store']);
        Route::put('/update/{id}', [BookController::class, 'update']);
        Route::delete('/delete/{id}', [BookController::class, 'destroy']);
    });

    Route::group([
        "prefix" => "book-details"
    ], function () {
        Route::get('/', [BookDetailController::class, 'index']);
        Route::get('/{id}', [BookDetailController::class, 'show']);
        Route::post('/create', [BookDetailController::class, 'store']);
        Route::put('/update/{id}', [BookDetailController::class, 'update']);
        Route::delete('/delete/{id}', [BookDetailController::class, 'destroy']);
    });

    Route::group([
        "prefix" => "authors"
    ], function () {
        Route::get('/', [AuthorController::class, 'index']);
        Route::get('/{id}', [AuthorController::class, 'show']);
        Route::post('/create', [AuthorController::class, 'store']);
        Route::put('/update/{id}', [AuthorController::class, 'update']);
        Route::delete('/delete/{id}', [AuthorController::class, 'destroy']);
    });
});
