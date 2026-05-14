<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse(string $message, mixed $data = null, int $statusCode = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'meta' => [
                'service_name' => config('app.service_name', env('SERVICE_NAME', 'Route-Schedule-Service')),
                'api_version' => config('app.api_version', env('API_VERSION', 'v1')),
            ],
        ], $statusCode);
    }

    protected function errorResponse(string $message, mixed $errors = null, int $statusCode = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}