<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RouteController extends Controller
{
    use ApiResponse;

    #[\OpenApi\Attributes\Get(
        path: '/api/v1/routes',
        summary: 'Get all routes',
        security: [['IAEApiKey' => []]],
        tags: ['Routes'],
        responses: [
            new \OpenApi\Attributes\Response(response: 200, description: 'Routes retrieved successfully'),
            new \OpenApi\Attributes\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function index()
    {
        $routes = Route::latest()->get();

        return $this->successResponse(
            'Routes retrieved successfully',
            $routes,
            200
        );
    }

    #[\OpenApi\Attributes\Get(
        path: '/api/v1/routes/{id}',
        summary: 'Get route by ID',
        security: [['IAEApiKey' => []]],
        tags: ['Routes'],
        parameters: [
            new \OpenApi\Attributes\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new \OpenApi\Attributes\Schema(type: 'integer')
            )
        ],
        responses: [
            new \OpenApi\Attributes\Response(response: 200, description: 'Route retrieved successfully'),
            new \OpenApi\Attributes\Response(response: 401, description: 'Unauthorized'),
            new \OpenApi\Attributes\Response(response: 404, description: 'Route not found')
        ]
    )]

    public function show($id)
    {
        $route = Route::find($id);

        if (!$route) {
            return $this->errorResponse('Route not found', null, 404);
        }

        return $this->successResponse(
            'Route retrieved successfully',
            $route,
            200
        );
    }

    #[\OpenApi\Attributes\Post(
        path: '/api/v1/routes',
        summary: 'Create new route',
        security: [['IAEApiKey' => []]],
        tags: ['Routes'],
        requestBody: new \OpenApi\Attributes\RequestBody(
            required: true,
            content: new \OpenApi\Attributes\JsonContent(
                required: [
                    'route_code',
                    'origin',
                    'destination',
                    'departure_point',
                    'arrival_point',
                    'departure_date',
                    'departure_time',
                    'arrival_time',
                    'vehicle_type',
                    'price',
                    'seat_capacity',
                    'available_seats',
                    'status'
                ],
                properties: [
                    new \OpenApi\Attributes\Property(property: 'route_code', type: 'string', example: 'BDG-JKT-003'),
                    new \OpenApi\Attributes\Property(property: 'origin', type: 'string', example: 'Bandung'),
                    new \OpenApi\Attributes\Property(property: 'destination', type: 'string', example: 'Jakarta'),
                    new \OpenApi\Attributes\Property(property: 'departure_point', type: 'string', example: 'Pool Dago'),
                    new \OpenApi\Attributes\Property(property: 'arrival_point', type: 'string', example: 'Terminal Lebak Bulus'),
                    new \OpenApi\Attributes\Property(property: 'departure_date', type: 'string', example: '2026-05-23'),
                    new \OpenApi\Attributes\Property(property: 'departure_time', type: 'string', example: '09:00'),
                    new \OpenApi\Attributes\Property(property: 'arrival_time', type: 'string', example: '12:30'),
                    new \OpenApi\Attributes\Property(property: 'vehicle_type', type: 'string', example: 'Travel Executive'),
                    new \OpenApi\Attributes\Property(property: 'price', type: 'integer', example: 150000),
                    new \OpenApi\Attributes\Property(property: 'seat_capacity', type: 'integer', example: 12),
                    new \OpenApi\Attributes\Property(property: 'available_seats', type: 'integer', example: 12),
                    new \OpenApi\Attributes\Property(property: 'status', type: 'string', example: 'available')
                ]
            )
        ),
        responses: [
            new \OpenApi\Attributes\Response(response: 201, description: 'Route created successfully'),
            new \OpenApi\Attributes\Response(response: 401, description: 'Unauthorized'),
            new \OpenApi\Attributes\Response(response: 422, description: 'Validation failed')
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_code' => ['required', 'string', 'max:100', 'unique:routes,route_code'],
            'origin' => ['required', 'string', 'max:150'],
            'destination' => ['required', 'string', 'max:150'],
            'departure_point' => ['required', 'string', 'max:150'],
            'arrival_point' => ['required', 'string', 'max:150'],
            'departure_date' => ['required', 'date'],
            'departure_time' => ['required', 'date_format:H:i'],
            'arrival_time' => ['required', 'date_format:H:i'],
            'vehicle_type' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:0'],
            'seat_capacity' => ['required', 'integer', 'min:1'],
            'available_seats' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['available', 'full', 'inactive', 'delayed'])],
        ]);

        if ($validated['available_seats'] > $validated['seat_capacity']) {
            return $this->errorResponse('Validation failed', [
                'available_seats' => ['Available seats cannot be greater than seat capacity.']
            ], 422);
        }

        $route = Route::create($validated);

        return $this->successResponse(
            'Route created successfully',
            $route,
            201
        );
    }
}