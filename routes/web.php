<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Http\Controllers\GoogleAuthController;

// Home route
Route::get("/", function () {
    return "Synthera Backend Running";
});


// 1. Cek koneksi database
Route::get('/tes-koneksi', function () {
    try {
        $tables = DB::select('SHOW TABLES');
        return response()->json([
            'status' => 'Koneksi Berhasil!',
            'database' => DB::getDatabaseName(),
            'daftar_tabel' => $tables
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'Gagal konek!',
            'error' => $e->getMessage()
        ], 500);
    }
});


// 2. Test insert category
Route::get('/test-category', function () {
    try {
        $category = Category::create([
            'name' => 'Web Development',
            'slug' => 'web-development',
            'description' => 'Belajar Laravel dari Nol',
        ]);

        return response()->json([
            'status' => 'Sukses simpan ke database!',
            'data_baru' => $category
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'Gagal simpan!',
            'error' => $e->getMessage()
        ], 500);
    }
});


// 🔐 Google OAuth Routes (FINAL)
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);