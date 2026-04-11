<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleAuthController extends Controller
{
    // Redirect ke Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Callback dari Google
    public function handleGoogleCallback()
    {
        try {
            // Ambil data user dari Google
            $googleUser = Socialite::driver('google')->user();

            // Cari atau buat user
            $user = User::updateOrCreate(
                [
                    'email' => $googleUser->email
                ],
                [
                    'name' => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'password' => bcrypt('google-login') // dummy password
                ]
            );

            // Buat token Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            // Redirect ke FRONTEND + bawa token
            return redirect("https://synthera.id/login?token=" . $token);

        } catch (Exception $e) {
            // Log error
            Log::error($e->getMessage());

            // Redirect kalau gagal
            return redirect("https://synthera.id/login?error=login_failed");
        }
    }
}