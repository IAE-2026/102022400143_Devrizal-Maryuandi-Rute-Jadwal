<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'iae.key' => \App\Http\Middleware\CheckIaeApiKey::class,
        ]);

        // Force every API response to comply with the IAE-T2 contract
        // (Content-Type: application/json + JSON error handling).
        $middleware->appendToGroup('api', [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Validation errors (422) -> IAE-T2 error wrapper.
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Any other exception (404 not found, 405, 500, etc.) on api/* ->
        // IAE-T2 error wrapper, so the detector always sees the standard shape.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $statusCode = $e instanceof HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            $message = $e->getMessage();
            if ($message === '' || $statusCode === 404) {
                $message = $statusCode === 404
                    ? 'Resource not found'
                    : 'Internal server error';
            }

            return response()->json([
                'status' => 'error',
                'message' => $message,
                'errors' => null,
            ], $statusCode);
        });
    })->create();

