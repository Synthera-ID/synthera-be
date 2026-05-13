<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController;

// Home route
Route::get("/", function () {
    return "Synthera Backend Running";
});

// 🔐 Google OAuth Routes (FINAL)
Route::get('/v1/api/oauth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/v1/api/oauth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

