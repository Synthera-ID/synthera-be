<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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