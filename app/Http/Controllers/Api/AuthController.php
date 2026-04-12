<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

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

        if (!$token || !Cache::has("oauth_$token")) {
            return response()->json([
                'message' => 'Invalid Token Authentication.'
            ], 401);
        }

        // 🔥 ambil user id
        $userId = Cache::get("oauth_$token");

        Cache::forget("oauth_$token");

        return response()->json([
            'success' => true,
            'message' => "Authentication Successfully.",
            'user_id' => $userId
        ]);
    }
}
