<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Http\Resources\UserResource;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PlanFeatureController;
use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\ApiKeyController;
use App\Http\Controllers\Api\ApiUsageController;
use App\Http\Controllers\TwoFactorController;

/*
|--------------------------------------------------------------------------
| AUTH (Public)
|--------------------------------------------------------------------------
*/

Route::post('/auth/verify', [
    AuthController::class,
    'verify'
]);

Route::post('/login', [
    AuthController::class,
    'login'
]);

/*
|--------------------------------------------------------------------------
| COURSE (Public)
|--------------------------------------------------------------------------
*/

Route::get('/courses', [
    CourseController::class,
    'index'
]);

Route::get('/courses/{id}', [
    CourseController::class,
    'show'
]);

Route::get('/users/{id}', [
    UserController::class,
    'show'
]);

Route::put('/users/{id}', [
    UserController::class,
    'update'
]);

Route::get('/memberships', [
    MembershipController::class,
    'index'
]);

Route::get('/plans', [
    SubscriptionPlanController::class,
    'index'
]);

Route::get('/subscriptions', [
    SubscriptionPlanController::class,
    'index'
]);

Route::get('/transactions', [
    TransactionController::class,
    'index'
]);

Route::get(
    '/transactions/{invoice_code}/status',
    [TransactionController::class, 'checkStatus']
);

Route::post(
    '/payment/callback',
    [PaymentController::class, 'callback']
);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        $user = $request->user()
            ->load('membership.subscription');

        return new UserResource($user);
    });

    Route::patch('/user', function (Request $request) {

        if ($request->type === "change_profile") {

            $request->validate([
                'phone' => [
                    'required',
                    'regex:/^(08|\+628)[0-9]{8,13}$/',
                ],
            ]);

            $user = $request->user();

            $user->update([
                'phone' => $request->phone,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phone updated successfully',
                'data' => $user,
            ]);
        }

        if ($request->type === "change_password") {

            $request->validate([
                'current_password' => ['required'],
                'new_password' => [
                    'required',
                    'min:8',
                    'confirmed'
                ],
                'type' => ['required']
            ]);

            $user = $request->user();

            if (!Hash::check(
                $request->current_password,
                $user->password
            )) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                ], 422);
            }

            $user->update([
                'password' => Hash::make(
                    $request->new_password
                ),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully',
            ]);
        }
    });

    /*
    |--------------------------------------------------------------------------
    | API KEY
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/api-key/generate',
        [ApiKeyController::class, 'generate']
    );

    Route::get(
        '/api-key',
        [ApiKeyController::class, 'index']
    );

    Route::post('/transactions', [
        TransactionController::class,
        'store'
    ]);

    /*
    |--------------------------------------------------------------------------
    | 2FA
    |--------------------------------------------------------------------------
    */

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

Route::middleware([
    'auth:sanctum',
    'admin'
])->group(function () {

    // Subscription management
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

    Route::put(
        '/transactions/{id}',
        [TransactionController::class, 'update']
    );

    Route::delete(
        '/transactions/{id}',
        [TransactionController::class, 'destroy']
    );

    // User management CRUD
    Route::apiResource(
        'admin/users',
        UserManagementController::class
    );

    /*
    |--------------------------------------------------------------------------
    | API USAGE MONITORING
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/api-usage',
        [ApiUsageController::class, 'index']
    );
});

/*
|--------------------------------------------------------------------------
| PUBLIC COURSE API (API KEY REQUIRED)
|--------------------------------------------------------------------------
*/

Route::middleware('api.key')->group(function () {

    Route::get(
        '/public/courses',
        [CourseController::class, 'index']
    );

    Route::get(
        '/public/courses/{id}',
        [CourseController::class, 'show']
    );
});
