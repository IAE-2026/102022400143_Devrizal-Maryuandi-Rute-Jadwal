<?php

use App\Http\Controllers\Api\RouteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('iae.key')
    ->group(function () {
        Route::get('/routes', [RouteController::class, 'index']);
        Route::get('/routes/{id}', [RouteController::class, 'show']);
        Route::post('/routes', [RouteController::class, 'store']);
    });

// Catch-all for any unmatched API route -> IAE-T2 error wrapper.
Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Resource not found',
        'errors' => null,
    ], 404);
});