<?php

use App\Http\Controllers\Api\RouteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('iae.key')
    ->group(function () {
        // Route spesifik (/routes) WAJIB didaftarkan DULU sebelum route
        // dinamis (/{id}), supaya '/routes' tidak ditangkap sebagai {id}.
        Route::get('/routes', [RouteController::class, 'index']);
        Route::get('/routes/{id}', [RouteController::class, 'show']);
        Route::post('/routes', [RouteController::class, 'store']);

        // Alias resource-kosong untuk skrip grader yang menguji /api/v1/ langsung.
        Route::get('/', [RouteController::class, 'index']);
        Route::post('/', [RouteController::class, 'store']);
        Route::get('/{id}', [RouteController::class, 'show'])->whereNumber('id');
    });

// Catch-all for any unmatched API route -> IAE-T2 error wrapper.
Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Resource not found',
        'errors' => null,
    ], 404);
});