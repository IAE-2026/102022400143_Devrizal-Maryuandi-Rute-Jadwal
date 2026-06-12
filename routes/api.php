<?php

use App\Http\Controllers\Api\RouteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('iae.key')
    ->group(function () {
        Route::get('/routes', [RouteController::class, 'index']);
        Route::get('/routes/{id}', [RouteController::class, 'show']);
        Route::post('/routes', [RouteController::class, 'store']);
        Route::put('/routes/{id}', [RouteController::class, 'update']);
        Route::delete('/routes/{id}', [RouteController::class, 'destroy']);
        Route::post('/routes/{id}/reset-seats', [RouteController::class, 'resetSeats']);

        // Critical transaction — butuh JWT SSO tambahan
        Route::post('/routes/{id}/reserve-seats', [RouteController::class, 'reserveSeats'])
            ->middleware('sso.jwt');
    });