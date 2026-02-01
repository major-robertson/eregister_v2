<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * Validates the Bearer token in the Authorization header
     * against the configured NOSYNELLY_API_KEY.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = config('services.nosynelly.api_key');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'error' => 'API key not configured.',
            ], 500);
        }

        $providedKey = $request->bearerToken();

        if (! $providedKey || ! hash_equals($apiKey, $providedKey)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid or missing API key.',
            ], 401);
        }

        return $next($request);
    }
}
