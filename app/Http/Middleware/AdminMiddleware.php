<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle( $request, Closure $next)
    {
        // Check if the authenticated user is an admin
        if (strtolower($request->user()->role) !== 'admin') {
            return response()->json([
                'message' => 'Access denied.'
            ], 403);
        }

        return $next($request);
    }
}
