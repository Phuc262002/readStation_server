<?php

use App\Http\Controllers\Api\Admin\AuthorController as AdminAuthorController;
use App\Http\Controllers\Api\Admin\BookcaseController;
use App\Http\Controllers\Api\Admin\BookController as AdminBookController;
use App\Http\Controllers\Api\Admin\BookDetailController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\InvoiceEnterController;
use App\Http\Controllers\Api\Admin\PostController as AdminPostController;
use App\Http\Controllers\Api\Admin\PublishingCompanyController as AdminPublishingCompanyController;
use App\Http\Controllers\Api\Admin\ShelveController;
use App\Http\Controllers\Api\Admin\SupplierController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\BotTelegram\GithubActionController;
use App\Http\Controllers\Api\CheckSchedule\RemindReturnBookController;
use App\Http\Controllers\Api\Client\AccountController;
use App\Http\Controllers\Api\Client\CommentController;
use App\Http\Controllers\Api\Client\OrderController;
use App\Http\Controllers\Api\Client\PostController;
use App\Http\Controllers\Api\Public\AuthorController;
use App\Http\Controllers\Api\Public\BookController;
use App\Http\Controllers\Api\Public\CategoryController;
use App\Http\Controllers\Api\Public\HomeController as PublicHomeController;
use App\Http\Controllers\Api\Public\PostController as PublicPostController;
use App\Http\Controllers\Api\Public\PublishingCompanyController;
use App\Http\Controllers\Api\Upload\CloudinaryController;
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
        "prefix" => "users",
        "middleware" => ["auth:api"]
    ], function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/get-one/{id}', [UserController::class, 'show']);
        Route::post('/create', [UserController::class, 'store']);
        Route::put('/update/{id}', [UserController::class, 'update']);
        Route::delete('/delete/{id}', [UserController::class, 'delete']);
    });

    Route::group([
        "prefix" => "account",
        "middleware" => ["auth:api"]
    ], function () {
        Route::get('/get-profile', [AccountController::class, 'userProfile']);
        Route::put('/update-profile', [AccountController::class, 'updateProfile']);

        Route::get('/', [OrderController::class, 'index']);
        Route::get('/get-one/{order}', [OrderController::class, 'show']);

        Route::get('/get-posts', [PostController::class, 'getPostAccount']);

        Route::group([
            "prefix" => "order"
        ], function () {
            Route::get('/get-all', [OrderController::class, 'index']);
            Route::get('/get-one/{id}', [OrderController::class, 'show']);
            Route::post('/create', [OrderController::class, 'store']);
            Route::put('/update/{order}', [OrderController::class, 'update']);
        });
    });

    Route::group([
        "prefix" => "home"
    ], function () {
        Route::get('/get-feautured-author', [PublicHomeController::class, 'getFeaturedAuthor']);
        Route::get('/get-feautured-book', [PublicHomeController::class, 'getFeaturedBook']);
        Route::get('/get-feautured-category', [PublicHomeController::class, 'getFeaturedCategory']);
        Route::get('/get-recommend-book', [PublicHomeController::class, 'bookRecommend']);
        Route::get('/get-book-lastest', [PublicHomeController::class, 'bookLatest']);
        Route::get('/get-statistic', [PublicHomeController::class, 'statisticHome']);
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
        "prefix" => "publishing-companies"
    ], function () {
        Route::get('/', [PublishingCompanyController::class, 'index']);

        Route::group(["middleware" => ["auth:api"]], function () {
            Route::get('/admin/get-all', [AdminPublishingCompanyController::class, 'getAllPublishingCompany']);
            Route::get('/get-one/{id}', [AdminPublishingCompanyController::class, 'show']);
            Route::post('/create', [AdminPublishingCompanyController::class, 'store']);
            Route::put('/update/{id}', [AdminPublishingCompanyController::class, 'update']);
            Route::delete('/delete/{id}', [AdminPublishingCompanyController::class, 'destroy']);
        });
    });

    Route::group([
        "prefix" => "bookcases",
        "middleware" => ["auth:api"]
    ], function () {
        Route::get('/', [BookcaseController::class, 'index']);
        Route::get('/get-one/{id}', [BookcaseController::class, 'show']);
        Route::post('/create', [BookcaseController::class, 'store']);
        Route::put('/update/{id}', [BookcaseController::class, 'update']);
        Route::delete('/delete/{id}', [BookcaseController::class, 'destroy']);
    });

    Route::group([
        "prefix" => "shelves",
        "middleware" => ["auth:api"]
    ], function () {
        Route::get('/', [ShelveController::class, 'index']);
        Route::get('/get-one/{id}', [ShelveController::class, 'show']);
        Route::post('/create', [ShelveController::class, 'store']);
        Route::put('/update/{id}', [ShelveController::class, 'update']);
        Route::delete('/delete/{id}', [ShelveController::class, 'destroy']);
    });

    Route::group([
        "prefix" => "books"
    ], function () {
        Route::get('/', [BookController::class, 'index']);
        Route::get('/get-one/{slug}', [BookController::class, 'show']);

        Route::group(["middleware" => ["auth:api"]], function () {
            Route::get('/admin/get-one/{id}', [AdminBookController::class, 'show']);
            Route::get('/admin/get-all', [AdminBookController::class, 'getAllBook']);
            Route::post('/create', [AdminBookController::class, 'store']);
            Route::post('/create-full', [AdminBookController::class, 'createFullBook']);
            Route::put('/update/{id}', [AdminBookController::class, 'update']);
            Route::delete('/delete/{id}', [AdminBookController::class, 'destroy']);
        });
    });

    Route::group([
        "prefix" => "posts"
    ], function () {
        Route::get('/', [PublicPostController::class, 'index']);
        Route::get('/get-one/{slug}', [PublicPostController::class, 'show']);

        Route::group(["middleware" => ["auth:api"]], function () {
            Route::post('/create', [PostController::class, 'store']);
            Route::get('/get/{id}', [PostController::class, 'show']);
            Route::put('/update/{id}', [PostController::class, 'update']);
            Route::delete('/delete/{id}', [PostController::class, 'destroy']);

            Route::get('/admin/get-all', [AdminPostController::class, 'index']);
            Route::put('/admin/update/{id}', [AdminPostController::class, 'update']);
        });
    });

    Route::group([
        "prefix" => "comments"
    ], function () {
        Route::get('/', [CommentController::class, 'index']);

        Route::group(["middleware" => ["auth:api"]], function () {
            Route::post('/create', [CommentController::class, 'store']);
            Route::put('/update/{id}', [CommentController::class, 'update']);
            Route::delete('/delete/{id}', [CommentController::class, 'destroy']);
        });
    });

    Route::group([
        "prefix" => "book-details",
        "middleware" => ["auth:api"]
    ], function () {
        Route::get('/', [BookDetailController::class, 'index']);
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

    Route::group([
        "prefix" => "suppliers",
        "middleware" => ["auth:api"]
    ], function () {
        Route::get('/', [SupplierController::class, 'index']);
        Route::get('/get-one/{id}', [SupplierController::class, 'show']);
        Route::post('/create', [SupplierController::class, 'store']);
        Route::put('/update/{id}', [SupplierController::class, 'update']);
        Route::delete('/delete/{id}', [SupplierController::class, 'destroy']);
    });

    Route::group([
        "prefix" => "invoice-enters",
        "middleware" => ["auth:api"]
    ], function () {
        Route::get('/', [InvoiceEnterController::class, 'index']);
        Route::get('/get-one/{id}', [InvoiceEnterController::class, 'show']);
        Route::post('/create', [InvoiceEnterController::class, 'store']);
        Route::put('/update/{id}', [InvoiceEnterController::class, 'update']);
    });

    Route::group([
        "prefix" => "telegram"
    ], function () {
        Route::post('/github-actions', [GithubActionController::class, 'githubActions']);
    });

    Route::group([
        "prefix" => "check-schedule"
    ], function () {
        Route::get('/remind-return-book', [RemindReturnBookController::class, 'remindReturnBook']);
    });

    Route::group([
        "prefix" => "upload",
        "middleware" => ["auth:api"]
    ], function () {
        Route::get('/images', [CloudinaryController::class, 'getAllImages']);
        Route::post('/images', [CloudinaryController::class, 'upload']);
        Route::delete('/images/delete/{publicId}', [CloudinaryController::class, 'deleteImage']);
    });
});
