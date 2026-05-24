<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController;

// Home route
Route::get("/", function () {
    return "Synthera Backend Running";
});

Route::get("/test", function() {
    $stringToSign = "DS29799" . "test1234_synthera" . 25000;
    return hash_hmac('sha256', $stringToSign, "a79cf130f48cfd633dd4ac63a0c0ffda");
});
// 🔐 Google OAuth Routes (FINAL)
Route::get('/v1/api/oauth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/v1/api/oauth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
