<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
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
            Route::post('/change-pass', [AuthController::class, 'changePassWord']);
        });
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
});
