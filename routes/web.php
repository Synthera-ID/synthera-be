<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/tes-koneksi', function () {
    try {
        // Mengambil daftar tabel langsung dari database localhost
        $tables = DB::select('SHOW TABLES');
        
        return response()->json([
            'status' => 'Koneksi Berhasil!',
            'database' => DB::getDatabaseName(),
            'daftar_tabel' => $tables
        ]);
    } catch (\Exception $e) {
        return "Gagal konek ke phpMyAdmin: " . $e->getMessage();
    }
});