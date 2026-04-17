<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function verify(Request $request)
    {
        $token = $request->token;

        if (!$token) {
            return response()->json([
                'message' => 'Invalid Token Authentication.'
            ], 401);
        }

        $userId = Cache::get("oauth_$token");

        if (!$userId) {
            return response()->json([
                'message' => 'Invalid or Expired Token.'
            ], 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'User Not Found.'
            ], 404);
        }
        $bearerToken = $user->createToken('auth-token')->plainTextToken;
        Cache::forget("oauth_$token");
        Log::info("User authenticated via Google OAuth.", ["token" => $bearerToken]);
        return response()->json([
            'success' => true,
            'token' => $bearerToken,
            'two_factor_enabled' => $user->two_factor_enabled,
            'message' => "Authentication Successfully.",
        ]);
    }
}
