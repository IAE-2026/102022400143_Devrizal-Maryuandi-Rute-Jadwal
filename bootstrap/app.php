<?php

use App\Http\Middleware\CorsHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: '',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(CorsHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Bungkus semua error API ke dalam Standard Integration Contract
        // ({status, message, errors}) agar 404/405/422/500 tetap konsisten.
        $exceptions->render(function (Throwable $e, Request $request) {
            $wantsJson = $request->expectsJson()
                || $request->is('api/*')
                || $request->is('graphql');

            if (! $wantsJson) {
                return null; // halaman HTML (landing, Swagger UI) pakai handler bawaan
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors(),
                ], 422);
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            $message = match ($status) {
                401 => 'Tidak terotorisasi: header X-IAE-KEY wajib dikirim',
                403 => 'Akses ditolak',
                404 => 'Resource yang diminta tidak ditemukan',
                405 => 'Metode HTTP tidak diizinkan untuk endpoint ini',
                default => $status >= 500 ? 'Terjadi kesalahan internal pada server' : $e->getMessage(),
            };

            return response()->json([
                'status' => 'error',
                'message' => $message,
                'errors' => null,
            ], $status);
        });
    })->create();
