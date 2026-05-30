<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use App\Models\ApiUsageLog;
use Symfony\Component\HttpFoundation\Response;

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

        // save api usage log
        ApiUsageLog::create([
            'user_id' => $key->user_id,
            'membership_id' => null,
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'status_code' => $response->status(),
            'ip_address' => $request->ip(),
            'called_at' => now(),
        ]);

        return $response;
    }
}