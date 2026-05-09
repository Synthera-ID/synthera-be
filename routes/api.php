<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\TwoFactorController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::post('/auth/google', [AuthController::class, 'google']);
Route::post('/auth/verify', [AuthController::class, 'verify']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

Route::get('/memberships', [MembershipController::class, 'index']);
Route::get('/memberships/{id}', [MembershipController::class, 'show']);

Route::get('/plans', [SubscriptionPlanController::class, 'index']);
Route::get('/plans/{id}', [SubscriptionPlanController::class, 'show']);

Route::get('/subscriptions', [SubscriptionPlanController::class, 'index']);
Route::get('/subscriptions/{id}', [SubscriptionPlanController::class, 'show']);

Route::get('/transactions', [TransactionController::class, 'index']);
Route::get('/transactions/{id}', [TransactionController::class, 'show']);

Route::get('/payments', [PaymentController::class, 'index']);
Route::get('/payments/{id}', [PaymentController::class, 'show']);

Route::post('/payment', [PaymentController::class, 'postPayment']);
Route::post('/payment/callback', [PaymentController::class, 'callback']);
Route::post('/test', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'success' => true,
        'data' => $request->all()
    ]);
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    

     Route::post('/transactions',
        [TransactionController::class, 'store']);
    
    Route::get('/payment/{id}', [PaymentController::class, 'show']);

    Route::put('/transactions/{id}',
        [TransactionController::class, 'update']);

    Route::delete('/transactions/{id}',
        [TransactionController::class, 'destroy']);

    Route::post('/2fa/verify',
        [TwoFactorController::class, 'verify']);

    Route::post('/2fa/enable',
        [TwoFactorController::class, 'enable']);

    Route::post('/2fa/disable',
        [TwoFactorController::class, 'disable']);
});

/*
|--------------------------------------------------------------------------
| ADMIN ONLY
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    Route::post('/subscriptions',
        [SubscriptionPlanController::class, 'store']);

    Route::put('/subscriptions/{id}',
        [SubscriptionPlanController::class, 'update']);

    Route::delete('/subscriptions/{id}',
        [SubscriptionPlanController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| USER
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {

    $user = $request->user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthorized.'
        ], 401);
    }

    return new UserResource($user);
});