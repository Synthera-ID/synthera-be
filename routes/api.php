<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PlanFeatureController;
use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\Controller;

/*
|--------------------------------------------------------------------------
| AUTH (Public)
|--------------------------------------------------------------------------
*/
Route::post('/auth/verify', [AuthController::class, 'verify']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| COURSE (Public)
|--------------------------------------------------------------------------
*/
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);

/*
|--------------------------------------------------------------------------
| MEMBERSHIP (Public)
|--------------------------------------------------------------------------
*/
Route::get('/memberships', [MembershipController::class, 'index']);
Route::get('/memberships/{id}', [MembershipController::class, 'show']);

/*
|--------------------------------------------------------------------------
| SUBSCRIPTION PLANS (Public)
|--------------------------------------------------------------------------
*/
Route::get('/plans', [SubscriptionPlanController::class, 'index']);
Route::get('/subscriptions', [SubscriptionPlanController::class, 'index']);
Route::get('/subscriptions/{id}', [SubscriptionPlanController::class, 'show']);

/*
|--------------------------------------------------------------------------
| TRANSACTION STATUS (Public)
|--------------------------------------------------------------------------
*/
Route::get('/transactions/{invoice_code}/status', [TransactionController::class, 'checkStatus']);

/*
|--------------------------------------------------------------------------
| PAYMENT (Public)
|--------------------------------------------------------------------------
*/
Route::get('/payments', [PaymentController::class, 'index']);
Route::get('/payments/{id}', [PaymentController::class, 'show']);
Route::post('/payment/callback', [PaymentController::class, 'callback']);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | USER PROFILE
    |--------------------------------------------------------------------------
    */
    Route::get('/user', [UserController::class, 'index']);
    Route::patch('/user', [UserController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | PAYMENT (Authenticated)
    |--------------------------------------------------------------------------
    */
    Route::post('/payment', [PaymentController::class, 'postPayment']);

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION (User's own)
    |--------------------------------------------------------------------------
    */
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | TWO FACTOR AUTH
    |--------------------------------------------------------------------------
    */
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify']);
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable']);
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable']);

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES (auth:sanctum + AdminMiddleware)
    |--------------------------------------------------------------------------
    */
    Route::middleware(\App\Http\Middleware\AdminMiddleware::class)->prefix('admin')->group(function () {

        // User Management
        Route::apiResource('users', UserManagementController::class);

        // Payment Management (CRUD metode pembayaran)
        Route::get('/payments', [PaymentController::class, 'adminIndex']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::put('/payments/{id}', [PaymentController::class, 'update']);
        Route::delete('/payments/{id}', [PaymentController::class, 'destroy']);

        // Transaction Management (admin sees all transactions)
        Route::get('/transactions', [TransactionController::class, 'adminIndex']);
        Route::put('/transactions/{id}', [TransactionController::class, 'update']);
        Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);

        // Subscription Plan Management (CRUD plans)
        Route::get('/subscriptions', [SubscriptionPlanController::class, 'adminIndex']);
        Route::post('/subscriptions', [SubscriptionPlanController::class, 'store']);
        Route::put('/subscriptions/{id}', [SubscriptionPlanController::class, 'update']);
        Route::delete('/subscriptions/{id}', [SubscriptionPlanController::class, 'destroy']);

        // Membership Management (CRUD user memberships, upgrade/downgrade)
        Route::get('/memberships', [MembershipController::class, 'adminIndex']);
        Route::post('/memberships', [MembershipController::class, 'store']);
        Route::put('/memberships/{id}', [MembershipController::class, 'update']);
        Route::delete('/memberships/{id}', [MembershipController::class, 'destroy']);

        // Course Management (Digital Content CRUD)
        Route::get('/courses', [CourseController::class, 'adminIndex']);
        Route::post('/courses', [CourseController::class, 'store']);
        Route::put('/courses/{id}', [CourseController::class, 'update']);
        Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

        // Plan Feature Management (CRUD plan features)
        Route::get('/features', [PlanFeatureController::class, 'adminIndex']);
        Route::post('/features', [PlanFeatureController::class, 'store']);
        Route::put('/features/{id}', [PlanFeatureController::class, 'update']);
        Route::delete('/features/{id}', [PlanFeatureController::class, 'destroy']);
    });
});