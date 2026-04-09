<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;

Route::get("/", function () {
    return "";
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


// 3. Redirect ke Google
Route::get('/v1/api/oauth/google', function () {
    return Socialite::driver('google')->redirect();
});


// 4. Callback dari Google
Route::get('/v1/api/oauth/google/callback', function () {

    try {
        $googleUser = Socialite::driver('google')->user();

        // 🔥 FIX: cek google_id ATAU email
        $user = User::where('google_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)
            ->first();

        if (!$user) {
            // user baru
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'password' => bcrypt(Str::random(16)),
            ]);
        } else {
            // 🔥 FIX: update google_id kalau belum ada
            if (!$user->google_id) {
                $user->update([
                    'google_id' => $googleUser->id
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => $user
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Login gagal',
            'error' => $e->getMessage()
        ], 500);
    }
});
