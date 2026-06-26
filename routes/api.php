<?php

use App\Http\Controllers\DocsController;
use App\Http\Controllers\GraphqlController;
use App\Http\Controllers\RouteController;
use App\Http\Middleware\RequireIaeApiKey;
use Illuminate\Support\Facades\Route;

// CORS preflight (OPTIONS) sudah ditangani global oleh App\Http\Middleware\CorsHeaders.
// Tidak ada route catch-all "OPTIONS /{any}" supaya path tak dikenal tetap
// membalas 404 (Not Found), bukan 405 (Method Not Allowed).

Route::get('/api-docs', [DocsController::class, 'swaggerUi']);
Route::get('/api-docs/', [DocsController::class, 'swaggerUi']);
Route::get('/openapi.json', [DocsController::class, 'openApi']);

Route::get('/graphql', [GraphqlController::class, 'playground']);

Route::middleware(RequireIaeApiKey::class)->group(function (): void {
    Route::get('/api/v1/routes', [RouteController::class, 'index']);
    Route::get('/api/v1/routes/{id}', [RouteController::class, 'show']);
    Route::post('/api/v1/routes', [RouteController::class, 'store']);

    Route::post('/graphql', [GraphqlController::class, 'query']);
});
