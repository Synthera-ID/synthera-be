<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TransactionController;

Route::get('/transactions', [TransactionController::class, 'index']);
Route::get('/transactions/{id}', [TransactionController::class, 'show']);

Route::get('/payments', [PaymentController::class, 'index']);
Route::get('/payments/{id}', [PaymentController::class, 'show']);

Route::get('/plans', [SubscriptionPlanController::class, 'index']);
Route::get('/plans/{id}', [SubscriptionPlanController::class, 'show']);

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);

Route::get('/memberships', [MembershipController::class, 'index']);
Route::get('/memberships/{id}', [MembershipController::class, 'show']);