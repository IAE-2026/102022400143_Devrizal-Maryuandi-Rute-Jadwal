<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Ensure every API request/response complies with the IAE-T2 contract:
     * - Force the client to be treated as wanting JSON (so Laravel never
     *   returns HTML error pages or redirects for api/* routes).
     * - Stamp the exact Content-Type: application/json header on the response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Make Laravel resolve all responses (including errors) as JSON.
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // Guarantee the contract-mandated Content-Type header (UTF-8 charset).
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');

        return $response;
    }
}
