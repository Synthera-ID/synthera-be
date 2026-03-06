<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

// 1. Buat cek daftar tabel (ini yang tadi udah jalan)
Route::get('/tes-koneksi', function () {
    try {
        $tables = DB::select('SHOW TABLES');
        return response()->json([
            'status' => 'Koneksi Berhasil!',
            'database' => DB::getDatabaseName(),
            'daftar_tabel' => $tables
        ]);
    } catch (\Exception $e) {
        return "Gagal konek: " . $e->getMessage();
    }
});

// 2. Buat ngetes input data ke tabel course_categories
Route::get('/test-category', function () {
    try {
        // Ini bakal nge-create data baru di localhost lu
        $category = Category::create([
            'name' => 'Web Development',
            'description' => 'Belajar Laravel dari Nol',
            'is_active' => true
        ]);

        return response()->json([
            'status' => 'Sukses simpan ke database!',
            'data_baru' => $category
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'Gagal simpan!',
            'pesan_error' => $e->getMessage()
        ], 500);
    }
});