<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// This controller handles API key generation for users. It allows authenticated users to create new API keys that can be used for accessing protected API endpoints. Each API key is associated with a user and has a unique name for easy identification. The generated API key is a random string of 64 characters, ensuring security and uniqueness. Users can manage their API keys through the application, including viewing, regenerating, or deactivating them as needed.
class ApiKeyController extends Controller
{
    public function generate(Request $request)
    {
        $apiKey = ApiKey::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'api_key' => Str::random(64),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'API key generated successfully',
            'data' => $apiKey
        ]);
    }
}