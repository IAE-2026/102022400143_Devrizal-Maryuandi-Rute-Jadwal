<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Route;
use App\Services\MessagePublisherService;
use App\Services\SoapAuditService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RouteController extends Controller
{
    use ApiResponse;

    #[\OpenApi\Attributes\Get(
        path: '/api/v1/routes',
        summary: 'Search & list routes (filter + pagination)',
        security: [['IAEApiKey' => []]],
        tags: ['Routes'],
        parameters: [
            new \OpenApi\Attributes\Parameter(name: 'origin', in: 'query', required: false, schema: new \OpenApi\Attributes\Schema(type: 'string'), description: 'Filter kota asal (partial match)'),
            new \OpenApi\Attributes\Parameter(name: 'destination', in: 'query', required: false, schema: new \OpenApi\Attributes\Schema(type: 'string'), description: 'Filter kota tujuan (partial match)'),
            new \OpenApi\Attributes\Parameter(name: 'departure_date', in: 'query', required: false, schema: new \OpenApi\Attributes\Schema(type: 'string', format: 'date'), description: 'Filter tanggal keberangkatan (Y-m-d)'),
            new \OpenApi\Attributes\Parameter(name: 'vehicle_type', in: 'query', required: false, schema: new \OpenApi\Attributes\Schema(type: 'string'), description: 'Filter jenis kendaraan'),
            new \OpenApi\Attributes\Parameter(name: 'status', in: 'query', required: false, schema: new \OpenApi\Attributes\Schema(type: 'string', enum: ['available', 'full', 'inactive', 'delayed']), description: 'Filter status rute'),
            new \OpenApi\Attributes\Parameter(name: 'min_seats', in: 'query', required: false, schema: new \OpenApi\Attributes\Schema(type: 'integer'), description: 'Hanya rute dengan available_seats >= nilai ini'),
            new \OpenApi\Attributes\Parameter(name: 'per_page', in: 'query', required: false, schema: new \OpenApi\Attributes\Schema(type: 'integer'), description: 'Jika diisi, response dipaginasi (1-100 per halaman)'),
        ],
        responses: [
            new \OpenApi\Attributes\Response(response: 200, description: 'Routes retrieved successfully'),
            new \OpenApi\Attributes\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function index(Request $request)
    {
        $query = Route::query();

        // Filter pencarian (semua opsional) — subproses "Pencarian Rute & Jadwal"
        if ($request->filled('origin')) {
            $query->where('origin', 'like', '%' . $request->query('origin') . '%');
        }
        if ($request->filled('destination')) {
            $query->where('destination', 'like', '%' . $request->query('destination') . '%');
        }
        if ($request->filled('departure_date')) {
            $query->whereDate('departure_date', $request->query('departure_date'));
        }
        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->query('vehicle_type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        if ($request->filled('min_seats')) {
            $query->where('available_seats', '>=', (int) $request->query('min_seats'));
        }

        $query->latest();

        // Pagination opsional — aktif hanya jika per_page dikirim, sehingga
        // konsumen lama yang mengharapkan data berupa array tetap kompatibel.
        if ($request->filled('per_page')) {
            $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
            $paginated = $query->paginate($perPage);

            return $this->successResponse(
                'Routes retrieved successfully',
                [
                    'items' => $paginated->items(),
                    'pagination' => [
                        'current_page' => $paginated->currentPage(),
                        'per_page'     => $paginated->perPage(),
                        'total'        => $paginated->total(),
                        'last_page'    => $paginated->lastPage(),
                    ],
                ],
                200
            );
        }

        return $this->successResponse(
            'Routes retrieved successfully',
            $query->get(),
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

    #[\OpenApi\Attributes\Put(
        path: '/api/v1/routes/{id}',
        summary: 'Update a route',
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
        requestBody: new \OpenApi\Attributes\RequestBody(
            required: true,
            content: new \OpenApi\Attributes\JsonContent(
                properties: [
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
            new \OpenApi\Attributes\Response(response: 200, description: 'Route updated successfully'),
            new \OpenApi\Attributes\Response(response: 401, description: 'Unauthorized'),
            new \OpenApi\Attributes\Response(response: 404, description: 'Route not found'),
            new \OpenApi\Attributes\Response(response: 422, description: 'Validation failed')
        ]
    )]
    public function update(Request $request, $id)
    {
        $route = Route::find($id);

        if (!$route) {
            return $this->errorResponse('Route not found', null, 404);
        }

        $validated = $request->validate([
            'route_code' => ['sometimes', 'string', 'max:100', 'unique:routes,route_code,' . $id],
            'origin' => ['sometimes', 'string', 'max:150'],
            'destination' => ['sometimes', 'string', 'max:150'],
            'departure_point' => ['sometimes', 'string', 'max:150'],
            'arrival_point' => ['sometimes', 'string', 'max:150'],
            'departure_date' => ['sometimes', 'date'],
            'departure_time' => ['sometimes', 'date_format:H:i'],
            'arrival_time' => ['sometimes', 'date_format:H:i'],
            'vehicle_type' => ['sometimes', 'string', 'max:100'],
            'price' => ['sometimes', 'integer', 'min:0'],
            'seat_capacity' => ['sometimes', 'integer', 'min:1'],
            'available_seats' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['available', 'full', 'inactive', 'delayed'])],
        ]);

        // Validasi available_seats tidak melebihi seat_capacity
        $newCapacity = $validated['seat_capacity'] ?? $route->seat_capacity;
        $newAvailable = $validated['available_seats'] ?? $route->available_seats;

        if ($newAvailable > $newCapacity) {
            return $this->errorResponse('Validation failed', [
                'available_seats' => ['Available seats cannot be greater than seat capacity.']
            ], 422);
        }

        $route->update($validated);

        return $this->successResponse(
            'Route updated successfully',
            $route,
            200
        );
    }

    #[\OpenApi\Attributes\Delete(
        path: '/api/v1/routes/{id}',
        summary: 'Delete a route',
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
            new \OpenApi\Attributes\Response(response: 200, description: 'Route deleted successfully'),
            new \OpenApi\Attributes\Response(response: 401, description: 'Unauthorized'),
            new \OpenApi\Attributes\Response(response: 404, description: 'Route not found')
        ]
    )]
    public function destroy($id)
    {
        $route = Route::find($id);

        if (!$route) {
            return $this->errorResponse('Route not found', null, 404);
        }

        $route->delete();

        return $this->successResponse(
            'Route deleted successfully',
            null,
            200
        );
    }

    #[\OpenApi\Attributes\Post(
        path: '/api/v1/routes/{id}/reset-seats',
        summary: 'Reset available seats to full capacity',
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
            new \OpenApi\Attributes\Response(response: 200, description: 'Seats reset successfully'),
            new \OpenApi\Attributes\Response(response: 401, description: 'Unauthorized'),
            new \OpenApi\Attributes\Response(response: 404, description: 'Route not found')
        ]
    )]
    public function resetSeats($id)
    {
        $route = Route::find($id);

        if (!$route) {
            return $this->errorResponse('Route not found', null, 404);
        }

        $route->available_seats = $route->seat_capacity;
        $route->status = 'available';
        $route->save();

        return $this->successResponse(
            'Seats reset successfully. Available: ' . $route->available_seats . '/' . $route->seat_capacity,
            $route,
            200
        );
    }

    #[\OpenApi\Attributes\Post(
        path: '/api/v1/routes/{id}/reserve-seats',
        summary: 'Reserve seats on a route',
        security: [['IAEApiKey' => []], ['BearerAuth' => []]],
        tags: ['Routes'],
        parameters: [
            new \OpenApi\Attributes\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new \OpenApi\Attributes\Schema(type: 'integer')
            )
        ],
        requestBody: new \OpenApi\Attributes\RequestBody(
            required: true,
            content: new \OpenApi\Attributes\JsonContent(
                required: ['quantity', 'booking_reference'],
                properties: [
                    new \OpenApi\Attributes\Property(property: 'quantity', type: 'integer', example: 2),
                    new \OpenApi\Attributes\Property(property: 'booking_reference', type: 'string', example: 'BOOK-2026-0001'),
                ]
            )
        ),
        responses: [
            new \OpenApi\Attributes\Response(response: 200, description: 'Seats reserved successfully'),
            new \OpenApi\Attributes\Response(response: 401, description: 'Unauthorized - missing or invalid JWT'),
            new \OpenApi\Attributes\Response(response: 403, description: 'Forbidden - role tidak diizinkan'),
            new \OpenApi\Attributes\Response(response: 404, description: 'Route not found'),
            new \OpenApi\Attributes\Response(response: 409, description: 'Insufficient seats'),
            new \OpenApi\Attributes\Response(response: 422, description: 'Validation failed'),
            new \OpenApi\Attributes\Response(response: 502, description: 'SOAP audit gagal'),
        ]
    )]
    public function reserveSeats(Request $request, $id)
    {
        // ── 1. Cek role user ──────────────────────────────────────────────
        $authUser = $request->attributes->get('auth_user');
        $jwtToken = $request->attributes->get('jwt_token');

        $allowedRoles = ['admin', 'operator', 'service'];
        if (!$authUser || !in_array($authUser->local_role, $allowedRoles)) {
            return $this->errorResponse(
                'Forbidden: role ' . ($authUser->local_role ?? 'unknown') . ' tidak diizinkan melakukan reservasi',
                null,
                403
            );
        }

        // ── 2. Validasi request body ──────────────────────────────────────
        $validated = $request->validate([
            'quantity'          => ['required', 'integer', 'min:1'],
            'booking_reference' => ['required', 'string', 'max:100'],
        ]);

        // ── 3. Mulai DB transaction ───────────────────────────────────────
        try {
            $result = DB::transaction(function () use ($id, $validated, $jwtToken) {

                // 3a. Lock baris route (FOR UPDATE) — cegah race condition
                $route = Route::lockForUpdate()->find($id);

                if (!$route) {
                    throw new \Exception('ROUTE_NOT_FOUND');
                }

                if ($route->status !== 'available') {
                    throw new \Exception('ROUTE_NOT_AVAILABLE:' . $route->status);
                }

                if ($route->available_seats < $validated['quantity']) {
                    throw new \Exception('INSUFFICIENT_SEATS:' . $route->available_seats);
                }

                // 3b. Kirim SOAP audit — wajib sukses sebelum commit
                $soapService   = new SoapAuditService();
                $logData       = [
                    'route_id'          => $route->id,
                    'booking_reference' => $validated['booking_reference'],
                    'reserved_seats'    => $validated['quantity'],
                    'origin'            => $route->origin,
                    'destination'       => $route->destination,
                ];
                $requestPayload = $soapService->buildRequestPayload('RESERVE_SEATS', $logData);

                try {
                    $receiptNumber = $soapService->sendAudit($jwtToken, 'RESERVE_SEATS', $logData);
                } catch (\Exception $e) {
                    // Simpan audit log gagal
                    AuditLog::create([
                        'booking_reference' => $validated['booking_reference'],
                        'route_id'          => $route->id,
                        'reserved_seats'    => $validated['quantity'],
                        'action'            => 'RESERVE_SEATS',
                        'receipt_number'    => null,
                        'audit_status'      => 'FAILED',
                        'request_payload'   => $requestPayload,
                        'response_payload'  => $e->getMessage(),
                    ]);
                    throw new \Exception('SOAP_FAILED:' . $e->getMessage());
                }

                // 3c. Kurangi available_seats
                $route->available_seats -= $validated['quantity'];
                $route->save();

                // 3d. Simpan audit log sukses
                AuditLog::create([
                    'booking_reference' => $validated['booking_reference'],
                    'route_id'          => $route->id,
                    'reserved_seats'    => $validated['quantity'],
                    'action'            => 'RESERVE_SEATS',
                    'receipt_number'    => $receiptNumber,
                    'audit_status'      => 'SUCCESS',
                    'request_payload'   => $requestPayload,
                    'response_payload'  => null,
                ]);

                return [
                    'route'          => $route,
                    'receipt_number' => $receiptNumber,
                ];
            });

        } catch (\Exception $e) {
            $msg = $e->getMessage();

            if (str_starts_with($msg, 'ROUTE_NOT_FOUND')) {
                return $this->errorResponse('Route tidak ditemukan', null, 404);
            }
            if (str_starts_with($msg, 'ROUTE_NOT_AVAILABLE')) {
                return $this->errorResponse('Route tidak tersedia (status: ' . explode(':', $msg)[1] . ')', null, 409);
            }
            if (str_starts_with($msg, 'INSUFFICIENT_SEATS')) {
                return $this->errorResponse(
                    'Kursi tidak cukup. Tersedia: ' . explode(':', $msg)[1],
                    null,
                    409
                );
            }
            if (str_starts_with($msg, 'SOAP_FAILED')) {
                return $this->errorResponse('SOAP audit gagal: ' . explode(':', $msg, 2)[1], null, 502);
            }

            Log::error('[reserveSeats] Unexpected error', ['error' => $msg]);
            return $this->errorResponse('Terjadi kesalahan server', null, 500);
        }

        // ── 4. Publish event (async, tidak block response) ─────────────
        $publisher = new MessagePublisherService();
        $publisher->publishSeatReserved(
            bearerToken:      $jwtToken,
            routeId:          $result['route']->id,
            bookingReference: $validated['booking_reference'],
            reservedSeats:    $validated['quantity'],
            availableSeats:   $result['route']->available_seats,
            receiptNumber:    $result['receipt_number'],
        );

        // ── 5. Return response ────────────────────────────────────────────
        return $this->successResponse('Seats reserved successfully', [
            'route_id'             => $result['route']->id,
            'booking_reference'    => $validated['booking_reference'],
            'reserved_seats'       => $validated['quantity'],
            'available_seats'      => $result['route']->available_seats,
            'audit_receipt_number' => $result['receipt_number'],
        ]);
    }

    #[\OpenApi\Attributes\Post(
        path: '/api/v1/routes/{id}/release-seats',
        summary: 'Release (return) seats on a route — kompensasi pembatalan/gagal bayar',
        security: [['IAEApiKey' => []], ['BearerAuth' => []]],
        tags: ['Routes'],
        parameters: [
            new \OpenApi\Attributes\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new \OpenApi\Attributes\Schema(type: 'integer')
            )
        ],
        requestBody: new \OpenApi\Attributes\RequestBody(
            required: true,
            content: new \OpenApi\Attributes\JsonContent(
                required: ['quantity', 'booking_reference'],
                properties: [
                    new \OpenApi\Attributes\Property(property: 'quantity', type: 'integer', example: 2),
                    new \OpenApi\Attributes\Property(property: 'booking_reference', type: 'string', example: 'BOOK-2026-0001'),
                ]
            )
        ),
        responses: [
            new \OpenApi\Attributes\Response(response: 200, description: 'Seats released successfully'),
            new \OpenApi\Attributes\Response(response: 401, description: 'Unauthorized'),
            new \OpenApi\Attributes\Response(response: 403, description: 'Forbidden role'),
            new \OpenApi\Attributes\Response(response: 404, description: 'Route not found'),
            new \OpenApi\Attributes\Response(response: 409, description: 'Conflict — melebihi kapasitas'),
            new \OpenApi\Attributes\Response(response: 422, description: 'Invalid request body'),
            new \OpenApi\Attributes\Response(response: 502, description: 'Enterprise integration failure (SOAP)'),
        ]
    )]
    public function releaseSeats(Request $request, $id)
    {
        // ── 1. Cek role user ──────────────────────────────────────────────
        $authUser = $request->attributes->get('auth_user');
        $jwtToken = $request->attributes->get('jwt_token');

        $allowedRoles = ['admin', 'operator', 'service'];
        if (!$authUser || !in_array($authUser->local_role, $allowedRoles)) {
            return $this->errorResponse(
                'Forbidden: role ' . ($authUser->local_role ?? 'unknown') . ' tidak diizinkan melakukan pelepasan kursi',
                null,
                403
            );
        }

        // ── 2. Validasi request body ──────────────────────────────────────
        $validated = $request->validate([
            'quantity'          => ['required', 'integer', 'min:1'],
            'booking_reference' => ['required', 'string', 'max:100'],
        ]);

        // ── 3. Mulai DB transaction ───────────────────────────────────────
        try {
            $result = DB::transaction(function () use ($id, $validated, $jwtToken) {

                // 3a. Lock baris route (FOR UPDATE) — cegah race condition
                $route = Route::lockForUpdate()->find($id);

                if (!$route) {
                    throw new \Exception('ROUTE_NOT_FOUND');
                }

                // Guard: kursi yang dikembalikan tidak boleh melebihi kapasitas
                if (($route->available_seats + $validated['quantity']) > $route->seat_capacity) {
                    throw new \Exception('EXCEEDS_CAPACITY:' . $route->seat_capacity);
                }

                // 3b. Kirim SOAP audit — wajib sukses sebelum commit
                $soapService = new SoapAuditService();
                $logData     = [
                    'route_id'          => $route->id,
                    'booking_reference' => $validated['booking_reference'],
                    'released_seats'    => $validated['quantity'],
                    'origin'            => $route->origin,
                    'destination'       => $route->destination,
                ];
                $requestPayload = $soapService->buildRequestPayload('RELEASE_SEATS', $logData);

                try {
                    $receiptNumber = $soapService->sendAudit($jwtToken, 'RELEASE_SEATS', $logData);
                } catch (\Exception $e) {
                    AuditLog::create([
                        'booking_reference' => $validated['booking_reference'],
                        'route_id'          => $route->id,
                        'reserved_seats'    => $validated['quantity'],
                        'action'            => 'RELEASE_SEATS',
                        'receipt_number'    => null,
                        'audit_status'      => 'FAILED',
                        'request_payload'   => $requestPayload,
                        'response_payload'  => $e->getMessage(),
                    ]);
                    throw new \Exception('SOAP_FAILED:' . $e->getMessage());
                }

                // 3c. Tambah available_seats
                $route->available_seats += $validated['quantity'];
                // Jika sebelumnya 'full' dan kini ada kursi, kembalikan ke 'available'
                if ($route->status === 'full' && $route->available_seats > 0) {
                    $route->status = 'available';
                }
                $route->save();

                // 3d. Simpan audit log sukses
                AuditLog::create([
                    'booking_reference' => $validated['booking_reference'],
                    'route_id'          => $route->id,
                    'reserved_seats'    => $validated['quantity'],
                    'action'            => 'RELEASE_SEATS',
                    'receipt_number'    => $receiptNumber,
                    'audit_status'      => 'SUCCESS',
                    'request_payload'   => $requestPayload,
                    'response_payload'  => null,
                ]);

                return [
                    'route'          => $route,
                    'receipt_number' => $receiptNumber,
                ];
            });

        } catch (\Exception $e) {
            $msg = $e->getMessage();

            if (str_starts_with($msg, 'ROUTE_NOT_FOUND')) {
                return $this->errorResponse('Route tidak ditemukan', null, 404);
            }
            if (str_starts_with($msg, 'EXCEEDS_CAPACITY')) {
                return $this->errorResponse(
                    'Jumlah kursi yang dikembalikan melebihi kapasitas. Kapasitas: ' . explode(':', $msg)[1],
                    null,
                    409
                );
            }
            if (str_starts_with($msg, 'SOAP_FAILED')) {
                return $this->errorResponse('SOAP audit gagal: ' . explode(':', $msg, 2)[1], null, 502);
            }

            Log::error('[releaseSeats] Unexpected error', ['error' => $msg]);
            return $this->errorResponse('Terjadi kesalahan server', null, 500);
        }

        // ── 4. Publish event (async, tidak block response) ─────────────
        $publisher = new MessagePublisherService();
        $publisher->publishSeatReleased(
            bearerToken:      $jwtToken,
            routeId:          $result['route']->id,
            bookingReference: $validated['booking_reference'],
            releasedSeats:    $validated['quantity'],
            availableSeats:   $result['route']->available_seats,
            receiptNumber:    $result['receipt_number'],
        );

        // ── 5. Return response ────────────────────────────────────────────
        return $this->successResponse('Seats released successfully', [
            'route_id'             => $result['route']->id,
            'booking_reference'    => $validated['booking_reference'],
            'released_seats'       => $validated['quantity'],
            'available_seats'      => $result['route']->available_seats,
            'audit_receipt_number' => $result['receipt_number'],
        ]);
    }
}