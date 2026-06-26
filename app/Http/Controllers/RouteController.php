<?php

namespace App\Http\Controllers;

use App\Services\RouteStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RouteController extends Controller
{
    private const SERVICE_NAME = 'Rute-Jadwal-Service';
    private const API_VERSION = 'v1';

    private const VALID_STATUSES = ['available', 'full', 'inactive', 'delayed'];

    public function index(RouteStore $store): JsonResponse
    {
        $routes = $store->all();

        return $this->success('Data berhasil diambil', $routes, [
            'total' => count($routes),
        ]);
    }

    public function show(RouteStore $store, string $id): JsonResponse
    {
        $route = $store->find($id);

        if ($route === null) {
            return $this->error("Rute '{$id}' tidak ditemukan", 404);
        }

        return $this->success('Data berhasil diambil', $route);
    }

    public function store(Request $request, RouteStore $store): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
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
            'status' => ['required', Rule::in(self::VALID_STATUSES)],
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors()->toArray());
        }

        $validated = $validator->validated();

        if ($validated['available_seats'] > $validated['seat_capacity']) {
            return $this->error('Validasi gagal', 422, [
                'available_seats' => ['Kursi tersedia tidak boleh melebihi kapasitas kursi.'],
            ]);
        }

        $route = $store->create($validated);

        return $this->success('Rute berhasil dibuat', $route, [], 201);
    }

    private function success(string $message, mixed $data, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'meta' => array_merge([
                'service_name' => self::SERVICE_NAME,
                'api_version' => self::API_VERSION,
            ], $meta),
        ], $status);
    }

    private function error(string $message, int $status, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
