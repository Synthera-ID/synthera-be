<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Http\Resources\UserResource;

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
            $googleUser = Socialite::driver('google')->user();
            $avatarUrl = $googleUser->getAvatar();

            $user = User::where('google_id', $googleUser->id)->first();

            if (!$user) {
                $user = User::where('email', $googleUser->email)->first();
                if ($user) {
                    $user->update([
                        'google_id' => $googleUser->id
                    ]);
                }
            }

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $avatarUrl, // Sekarang variabel ini sudah aman digunakan
                    'password' => bcrypt(Str::random(8)),
                ]);
            }

            $token = Str::random(40);
            Cache::put("oauth_$token", $user->id, now()->addMinutes(1));
            return redirect()->away("https://synthera.id/login?status=success&token=$token");
        } catch (\Exception $e) {
            Log::info($e);
            return redirect()->away("https://synthera.id/login?status=failed");
        }
    }
}
