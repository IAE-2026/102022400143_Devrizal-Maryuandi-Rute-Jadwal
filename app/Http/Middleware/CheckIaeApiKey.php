<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIaeApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-IAE-KEY');
        $validApiKey = env('IAE_API_KEY', '102022400143');

        if (!$apiKey || $apiKey !== $validApiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Invalid or missing API Key',
                'errors' => null,
            ], 401);
        }

        return $next($request);
    }
}