<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use App\Models\ApiUsageLog;
use Symfony\Component\HttpFoundation\Response;
// This middleware checks for the presence of a valid API key in the request headers. If a valid API key is found, it allows the request to proceed and logs the API usage. If the API key is missing or invalid, it returns a 401 Unauthorized response. The middleware also updates the last used timestamp of the API key and records details of the API call in the ApiUsageLog for monitoring and analysis purposes.
class ApiKeyMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $apiKey = $request->header('X-API-KEY');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API Key is required'
            ], 401);
        }

        $key = ApiKey::where(
            'api_key',
            $apiKey
        )
        ->where('is_active', true)
        ->first();

        if (!$key) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API Key'
            ], 401);
        }

        $response = $next($request);

        // update last used
        $key->update([
            'last_used_at' => now()
        ]);

        ApiUsageLog::create([
            'user_id' => $key->user_id,
            'api_key_id' => $key->id,
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'status_code' => $response->status(),
            'ip_address' => $request->ip(),
            'called_at' => now(),
        ]);

        return $response;
    }
}
