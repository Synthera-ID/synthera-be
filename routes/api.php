<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\TwoFactorController;

// Route::post('/auth/google', [AuthController::class, 'google']);
// Route::get('/categories', [CategoryController::class, 'index']);
// Route::get('/categories/{id}', [CategoryController::class, 'show']);
// Route::get('/memberships/{id}', [MembershipController::class, 'show']);
// Route::get('/plans/{id}', [SubscriptionPlanController::class, 'show']);
// Route::get('/subscriptions/{id}', [SubscriptionPlanController::class, 'show']);
// Route::get('/transactions/{id}', [TransactionController::class, 'show']);
// Route::get('/payments', [PaymentController::class, 'index']);
// Route::get('/payments/{id}', [PaymentController::class, 'show']);
// Route::post('/payment', [PaymentController::class, 'postPayment']);
// Route::get('/payment/{id}', [PaymentController::class, 'show']);

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::post('/auth/verify', [AuthController::class, 'verify']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| COURSE ROUTES
|--------------------------------------------------------------------------
*/
    // Public
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);

    // Admin Only

        Route::post('/courses', [CourseController::class, 'store']);

        Route::put(
            '/courses/{id}',
            [CourseController::class, 'update']
        );

        Route::delete(
            '/courses/{id}',
            [CourseController::class, 'destroy']
        );

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// Membership
Route::get('/memberships', [MembershipController::class, 'index']);
Route::get('/memberships/{id}', [MembershipController::class, 'show']);

Route::post('/memberships', [MembershipController::class, 'store']);
Route::put('/memberships/{id}', [MembershipController::class, 'update']);
Route::delete('/memberships/{id}', [MembershipController::class, 'destroy']);

// Subscription
Route::get('/plans', [SubscriptionPlanController::class, 'index']);
Route::get('/subscriptions', [SubscriptionPlanController::class, 'index']);
Route::get('/subscriptions/{id}', [SubscriptionPlanController::class, 'show']);

// Transaction
Route::get('/transactions', [TransactionController::class, 'index']);
Route::get('/transactions/{id}', [TransactionController::class, 'show']);

Route::get(
    '/transactions/{invoice_code}/status',
    [TransactionController::class, 'checkStatus']
);

// Payment CRUD
Route::get('/payments', [PaymentController::class, 'index']);
Route::get('/payments/{id}', [PaymentController::class, 'show']);

Route::post('/payments', [PaymentController::class, 'store']);
Route::put('/payments/{id}', [PaymentController::class, 'update']);
Route::delete('/payments/{id}', [PaymentController::class, 'destroy']);

Route::post('/payment/callback', [PaymentController::class, 'callback']);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | USER
    |--------------------------------------------------------------------------
    */

    Route::get('/user', [UserController::class, 'index']);

    Route::patch('/user', [UserController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | PAYMENT
    |--------------------------------------------------------------------------
    */

    Route::post('/payment', [PaymentController::class, 'postPayment']);

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION
    |--------------------------------------------------------------------------
    */

    Route::post('/transactions', [TransactionController::class, 'store']);

    Route::put(
        '/transactions/{id}',
        [TransactionController::class, 'update']
    );

    Route::delete(
        '/transactions/{id}',
        [TransactionController::class, 'destroy']
    );

    /*
    |--------------------------------------------------------------------------
    | TWO FACTOR AUTH
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/2fa/verify',
        [TwoFactorController::class, 'verify']
    );

    Route::post(
        '/2fa/enable',
        [TwoFactorController::class, 'enable']
    );

    Route::post(
        '/2fa/disable',
        [TwoFactorController::class, 'disable']
    );
});

/*
|--------------------------------------------------------------------------
| ADMIN ONLY
|--------------------------------------------------------------------------
*/
Route::post('/courses', [CourseController::class, 'store']);

Route::put(
    '/courses/{id}',
    [CourseController::class, 'update']
);

Route::delete(
    '/courses/{id}',
    [CourseController::class, 'destroy']
);


    /*
    |--------------------------------------------------------------------------
    | MEMBERSHIP CRUD
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/memberships',
        [MembershipController::class, 'store']
    );

    Route::put(
        '/memberships/{id}',
        [MembershipController::class, 'update']
    );

    Route::delete(
        '/memberships/{id}',
        [MembershipController::class, 'destroy']
    );

    /*
    |--------------------------------------------------------------------------
    | PAYMENT CRUD
    |--------------------------------------------------------------------------
    */

    Route::post('/payments', [PaymentController::class, 'store']);

    Route::put(
        '/payments/{id}',
        [PaymentController::class, 'update']
    );

    Route::delete(
        '/payments/{id}',
        [PaymentController::class, 'destroy']
    );

    /*
    |--------------------------------------------------------------------------
    | SUBSCRIPTION CRUD
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/subscriptions',
        [SubscriptionPlanController::class, 'store']
    );

    Route::put(
        '/subscriptions/{id}',
        [SubscriptionPlanController::class, 'update']
    );

    Route::delete(
        '/subscriptions/{id}',
        [SubscriptionPlanController::class, 'destroy']
    );

    /*
    |--------------------------------------------------------------------------
    | USER MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'admin/users',
        UserManagementController::class
    );
