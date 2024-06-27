<?php

use App\Http\Controllers\Api\Admin\AuthorController as AdminAuthorController;
use App\Http\Controllers\Api\Admin\BookcaseController;
use App\Http\Controllers\Api\Admin\BookController as AdminBookController;
use App\Http\Controllers\Api\Admin\BookDetailController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\InvoiceEnterController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\PostController as AdminPostController;
use App\Http\Controllers\Api\Admin\PublishingCompanyController as AdminPublishingCompanyController;
use App\Http\Controllers\Api\Admin\ShelveController;
use App\Http\Controllers\Api\Admin\ShippingMethodController;
use App\Http\Controllers\Api\Admin\SupplierController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\WalletController as AdminWalletController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\BotTelegram\GithubActionController;
use App\Http\Controllers\Api\CheckSchedule\RemindReturnBookController;
use App\Http\Controllers\Api\Client\AccountController;
use App\Http\Controllers\Api\Client\CommentController;
use App\Http\Controllers\Api\Client\OrderController;
use App\Http\Controllers\Api\Client\PostController;
use App\Http\Controllers\Api\Client\WalletController;
use App\Http\Controllers\Api\PayOS\CheckCCCDController;
use App\Http\Controllers\Api\Public\AuthorController;
use App\Http\Controllers\Api\Public\BookController;
use App\Http\Controllers\Api\Public\CategoryController;
use App\Http\Controllers\Api\Public\HomeController as PublicHomeController;
use App\Http\Controllers\Api\Public\PostController as PublicPostController;
use App\Http\Controllers\Api\Public\PublishingCompanyController;
use App\Http\Controllers\Api\Public\ShippingMethodController as PublicShippingMethodController;
use App\Http\Controllers\Api\Shiip\ShiipController;
use App\Http\Controllers\Api\Upload\CloudinaryController;
use Illuminate\Support\Facades\Route;

Route::group([
    "prefix" => "v1"
], function () {

    // Authenticated routes
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

    // Account routes
    Route::group([
        "prefix" => "account",
        "middleware" => ["auth:api"]
    ], function () {
        Route::get('/get-profile', [AccountController::class, 'userProfile']);
        Route::put('/update-profile', [AccountController::class, 'updateProfile']);

        Route::get('/get-posts', [PostController::class, 'getPostAccount']);

        Route::group([
            "prefix" => "comments"
        ], function () {
            Route::get('/get-my-comments', [CommentController::class, 'getCommentAccount']);
            Route::get('/get-comments-my-post', [CommentController::class, 'getRepCommentAccount']);
        });

        Route::group([
            "prefix" => "wallet"
        ], function () {
            Route::get('/statistic', [WalletController::class, 'statistic']);
            Route::post('/create-transaction', [WalletController::class, 'storeDeposit']);
            Route::get('/get-payment-link/{transaction_code}', [WalletController::class, 'getPaymentLink']);
            Route::get('/transaction-history', [WalletController::class, 'transactionHistory']);
            Route::put('/update-transaction-status/{transaction_code}', [WalletController::class, 'updateTransactionStatus']);
            Route::post('/cancel-transaction/{transaction_code}', [WalletController::class, 'cancelPaymentLinkOfTransction']);
        });

        Route::group([
            "prefix" => "orders"
        ], function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::get('/{id}', [OrderController::class, 'show']);
            Route::post('/create', [OrderController::class, 'store']);
            Route::put('/cancel/{id}', [OrderController::class, 'cancelOrder']);
            Route::post('/extension/{id}', [OrderController::class, 'extensionAllOrder']);
        });
    });

    // Admin routes
    Route::group([
        "prefix" => "admin",
        "middleware" => ["auth:api"]
    ], function () {
        Route::group([
            "prefix" => "wallet",
        ], function () {
            Route::get('get-all', [AdminWalletController::class, 'index']);
            Route::post('create-deposit', [AdminWalletController::class, 'storeDeposit']);
            Route::get('get-user-wallet-transactions-history/{id}', [AdminWalletController::class, 'show']);
            Route::put('update-status/{id}', [AdminWalletController::class, 'update']);

            Route::put('update-transaction-status/{id}', [AdminWalletController::class, 'updateTransactionStatus']);
            Route::post('cancel-transaction/{transaction_code}', [AdminWalletController::class, 'cancelPaymentLinkOfTransction']);
            Route::get('get-payment-link/{transaction_code}', [AdminWalletController::class, 'getPaymentLink']);
        });

        Route::group([
            "prefix" => "users",
        ], function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/get-one/{id}', [UserController::class, 'show']);
            Route::post('/create', [UserController::class, 'store']);
            Route::put('/update/{id}', [UserController::class, 'update']);
            Route::delete('/delete/{id}', [UserController::class, 'delete']);
        });

        Route::group([
            "prefix" => "orders",
        ], function () {
            Route::get('/statistic', [AdminOrderController::class, 'statisticOrder']);
            Route::get('/', [AdminOrderController::class, 'index']);
            Route::get('/{id}', [AdminOrderController::class, 'show']);
        });

        Route::group([
            "prefix" => "categories"
        ], function () {
            Route::get('/', [AdminCategoryController::class, 'getAllCategory']);
            Route::get('/{id}', [AdminCategoryController::class, 'show']);
            Route::post('/create', [AdminCategoryController::class, 'store']);
            Route::put('/update/{id}', [AdminCategoryController::class, 'update']);
            Route::delete('/delete/{id}', [AdminCategoryController::class, 'destroy']);
        });

        Route::group([
            "prefix" => "publishing-companies"
        ], function () {
            Route::get('/', [AdminPublishingCompanyController::class, 'getAllPublishingCompany']);
            Route::get('/{id}', [AdminPublishingCompanyController::class, 'show']);
            Route::post('/create', [AdminPublishingCompanyController::class, 'store']);
            Route::put('/update/{id}', [AdminPublishingCompanyController::class, 'update']);
            Route::delete('/delete/{id}', [AdminPublishingCompanyController::class, 'destroy']);
        });

        Route::group([
            "prefix" => "authors"
        ], function () {
            Route::get('/', [AdminAuthorController::class, 'getAllAuthor']);
            Route::get('/{id}', [AdminAuthorController::class, 'show']);
            Route::post('/create', [AdminAuthorController::class, 'store']);
            Route::put('/update/{id}', [AdminAuthorController::class, 'update']);
            Route::delete('/delete/{id}', [AdminAuthorController::class, 'destroy']);
        });

        Route::group([
            "prefix" => "bookcases",
        ], function () {
            Route::get('/', [BookcaseController::class, 'index']);
            Route::get('/{id}', [BookcaseController::class, 'show']);
            Route::post('/create', [BookcaseController::class, 'store']);
            Route::put('/update/{id}', [BookcaseController::class, 'update']);
            Route::delete('/delete/{id}', [BookcaseController::class, 'destroy']);
        });

        Route::group([
            "prefix" => "shelves",
        ], function () {
            Route::get('/', [ShelveController::class, 'index']);
            Route::get('/{id}', [ShelveController::class, 'show']);
            Route::post('/create', [ShelveController::class, 'store']);
            Route::put('/update/{id}', [ShelveController::class, 'update']);
            Route::delete('/delete/{id}', [ShelveController::class, 'destroy']);
        });

        Route::group([
            "prefix" => "suppliers",
        ], function () {
            Route::get('/', [SupplierController::class, 'index']);
            Route::get('/{id}', [SupplierController::class, 'show']);
            Route::post('/create', [SupplierController::class, 'store']);
            Route::put('/update/{id}', [SupplierController::class, 'update']);
            Route::delete('/delete/{id}', [SupplierController::class, 'destroy']);
        });

        Route::group([
            "prefix" => "books"
        ], function () {
            Route::get('/', [AdminBookController::class, 'getAllBook']);
            Route::get('/{id}', [AdminBookController::class, 'show']);
            Route::post('/create', [AdminBookController::class, 'store']);
            Route::post('/create-full', [AdminBookController::class, 'createFullBook']);
            Route::put('/update/{id}', [AdminBookController::class, 'update']);
            Route::delete('/delete/{id}', [AdminBookController::class, 'destroy']);
        });

        Route::group([
            "prefix" => "book-details",
        ], function () {
            Route::get('/', [BookDetailController::class, 'index']);
            Route::get('/{id}', [BookDetailController::class, 'show']);
            Route::post('/create', [BookDetailController::class, 'store']);
            Route::put('/update/{id}', [BookDetailController::class, 'update']);
            Route::delete('/delete/{id}', [BookDetailController::class, 'destroy']);
        });



        Route::group([
            "prefix" => "invoice-enters",
        ], function () {
            Route::get('/', [InvoiceEnterController::class, 'index']);
            Route::get('/{id}', [InvoiceEnterController::class, 'show']);
            Route::post('/create', [InvoiceEnterController::class, 'store']);
            Route::put('/update/{id}', [InvoiceEnterController::class, 'update']);
        });

        Route::group([
            "prefix" => "posts"
        ], function () {
            Route::get('/', [AdminPostController::class, 'index']);
        });

        Route::group([
            "prefix" => "comments"
        ], function () {
            Route::get('/', [AdminCommentController::class, 'index']);
        });


        Route::group([
            "prefix" => "dashboard"
        ], function () {
            Route::get('/statistic-admin', [DashboardController::class, 'statisticAdmin']);
            Route::get('/book-hire-top-by-month', [DashboardController::class, 'bookHireTopByMonth']);
            Route::get('/invoice-enter-by-month', [DashboardController::class, 'invoiceEnterTopByMonth']);
        });

        Route::group([
            "prefix" => "shipping-methods"
        ], function () {
            Route::get('/', [ShippingMethodController::class, 'index']);
            Route::get('/{id}', [ShippingMethodController::class, 'show']);
            Route::post('/create', [ShippingMethodController::class, 'store']);
            Route::put('/update/{id}', [ShippingMethodController::class, 'update']);
            Route::delete('/delete/{id}', [ShippingMethodController::class, 'destroy']);
        });
    });



    // Public routes
    Route::group([
        "prefix" => "public"
    ], function () {
        // Home routes
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
            "prefix" => "books"
        ], function () {
            Route::get('/', [BookController::class, 'index']);
            Route::get('/{slug}', [BookController::class, 'show']);
        });

        Route::group([
            "prefix" => "posts"
        ], function () {
            Route::get('/', [PublicPostController::class, 'index']);
            Route::get('/{slug}', [PublicPostController::class, 'show']);
        });

        Route::group([
            "prefix" => "shiip",
        ], function () {
            Route::get('/province', [ShiipController::class, 'getProvince']);
            Route::get('/district', [ShiipController::class, 'getDistrict']);
            Route::get('/ward', [ShiipController::class, 'getWard']);
        });

        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/publishing-companies', [PublishingCompanyController::class, 'index']);
        Route::get('/authors', [AuthorController::class, 'index']);
        Route::get('/comments', [CommentController::class, 'index']);
        Route::get('/shipping-methods', [PublicShippingMethodController::class, 'index']);
    });


    // General routes has auth
    Route::group([
        "prefix" => "general",
        "middleware" => ["auth:api"]
    ], function () {
        Route::group([
            "prefix" => "posts"
        ], function () {
            Route::get('/{id}', [PostController::class, 'show']);
            Route::post('/create', [PostController::class, 'store']);
            Route::put('/update/{id}', [PostController::class, 'update']);
            Route::delete('/delete/{id}', [PostController::class, 'destroy']);
        });

        Route::group([
            "prefix" => "comments"
        ], function () {
            Route::post('/create', [CommentController::class, 'store']);
            Route::put('/update/{id}', [CommentController::class, 'update']);
            Route::delete('/delete/{id}', [CommentController::class, 'destroy']);
        });

        Route::post('/check-citizen', [CheckCCCDController::class, 'checkCCCD']);
    });

    Route::group([
        "prefix" => "telegram"
    ], function () {
        Route::post('/github-actions', [GithubActionController::class, 'githubActions']);
        Route::post('/github-actions-success', [GithubActionController::class, 'githubActionsSuccess']);
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

    Route::group([
        "prefix" => "shiip",
    ], function () {
        Route::get('/province', [ShiipController::class, 'getProvince']);
        Route::get('/district', [ShiipController::class, 'getDistrict']);
        Route::get('/ward', [ShiipController::class, 'getWard']);

        Route::post('/create-province', [ShiipController::class, 'createProvince']);
        Route::post('/create-district', [ShiipController::class, 'createDistrict']);
        Route::post('/create-ward', [ShiipController::class, 'createWard']);
    });
});
