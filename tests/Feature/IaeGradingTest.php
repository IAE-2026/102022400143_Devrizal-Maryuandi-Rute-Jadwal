<?php

namespace Tests\Feature;

use App\Models\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * IAE Tugas 2 — Grading Rubric Test
 *
 * Test ini memverifikasi semua 10 kriteria penilaian:
 * 1. Endpoint menolak request tanpa X-IAE-KEY → 401
 * 2. Request dengan X-IAE-KEY (NIM) berhasil → 200
 * 3. GET /api/v1/routes → 200 + JSON wrapper
 * 4. GET /api/v1/routes/{id} → 404 + error wrapper
 * 5. POST /api/v1/routes → 201 + JSON wrapper
 * 6. Swagger UI dapat diakses
 * 7. Swagger mencerminkan endpoint REST
 * 8. GraphQL endpoint dapat diakses
 * 9. Query GraphQL (introspection) berhasil
 * 10. Service berjalan (implicit — test ini jalan = service jalan)
 */
class IaeGradingTest extends TestCase
{
    use RefreshDatabase;

    private string $apiKey = '102022400143';

    // ─────────────────────────────────────────────────────────
    // Kriteria 1: Endpoint menolak request tanpa X-IAE-KEY
    // ─────────────────────────────────────────────────────────
    public function test_endpoint_rejects_request_without_api_key(): void
    {
        $response = $this->getJson('/api/v1/routes');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_endpoint_rejects_request_with_wrong_api_key(): void
    {
        $response = $this->getJson('/api/v1/routes', [
            'X-IAE-KEY' => 'wrong-key-123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    // ─────────────────────────────────────────────────────────
    // Kriteria 2: Request dengan X-IAE-KEY (NIM) berhasil
    // ─────────────────────────────────────────────────────────
    public function test_request_with_valid_api_key_succeeds(): void
    {
        $response = $this->getJson('/api/v1/routes', [
            'X-IAE-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────
    // Kriteria 3: GET /api/v1/routes → 200 + JSON wrapper
    // ─────────────────────────────────────────────────────────
    public function test_get_routes_returns_200_with_json_wrapper(): void
    {
        Route::factory()->create();

        $response = $this->getJson('/api/v1/routes', [
            'X-IAE-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'meta' => ['service_name', 'api_version'],
            ])
            ->assertJson([
                'status' => 'success',
            ]);
    }

    // ─────────────────────────────────────────────────────────
    // Kriteria 4: GET /api/v1/routes/{id} → 404 + error wrapper
    // ─────────────────────────────────────────────────────────
    public function test_get_nonexistent_route_returns_404_with_error_wrapper(): void
    {
        $response = $this->getJson('/api/v1/routes/99999', [
            'X-IAE-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(404)
            ->assertJsonStructure([
                'status',
                'message',
            ])
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_get_existing_route_returns_200(): void
    {
        $route = Route::factory()->create();

        $response = $this->getJson("/api/v1/routes/{$route->id}", [
            'X-IAE-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    // ─────────────────────────────────────────────────────────
    // Kriteria 5: POST /api/v1/routes → 201 + JSON wrapper
    // ─────────────────────────────────────────────────────────
    public function test_post_route_returns_201_with_json_wrapper(): void
    {
        $payload = [
            'route_code' => 'TEST-POST-001',
            'origin' => 'Bandung',
            'destination' => 'Jakarta',
            'departure_point' => 'Pool Dago',
            'arrival_point' => 'Terminal Lebak Bulus',
            'departure_date' => '2026-07-01',
            'departure_time' => '09:00',
            'arrival_time' => '12:30',
            'vehicle_type' => 'Travel Executive',
            'price' => 150000,
            'seat_capacity' => 12,
            'available_seats' => 10,
            'status' => 'available',
        ];

        $response = $this->postJson('/api/v1/routes', $payload, [
            'X-IAE-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'route_code'],
                'meta' => ['service_name', 'api_version'],
            ])
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('routes', [
            'route_code' => 'TEST-POST-001',
        ]);
    }

    public function test_post_route_with_invalid_data_returns_422(): void
    {
        // Kirim tanpa required fields
        $response = $this->postJson('/api/v1/routes', [], [
            'X-IAE-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation failed',
            ])
            ->assertJsonStructure([
                'errors',
            ]);
    }

    // ─────────────────────────────────────────────────────────
    // Kriteria 6: Swagger UI dapat diakses
    // ─────────────────────────────────────────────────────────
    public function test_swagger_ui_is_accessible(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────
    // Kriteria 7: Swagger mencerminkan endpoint REST
    // ─────────────────────────────────────────────────────────
    public function test_swagger_docs_endpoint_is_accessible(): void
    {
        // Generate swagger docs first
        $this->artisan('l5-swagger:generate');

        $response = $this->get('/docs');

        $response->assertStatus(200);

        $docs = $response->json();

        // Pastikan ada paths
        $this->assertArrayHasKey('paths', $docs);
        $this->assertNotEmpty($docs['paths']);

        // Hitung total operations (GET, POST, dll)
        $totalOps = 0;
        foreach ($docs['paths'] as $methods) {
            foreach ($methods as $method => $detail) {
                if (in_array($method, ['get', 'post', 'put', 'patch', 'delete'])) {
                    $totalOps++;
                }
            }
        }
        $this->assertGreaterThanOrEqual(3, $totalOps, 'Swagger harus punya minimal 3 operations');
    }

    // ─────────────────────────────────────────────────────────
    // Kriteria 8: GraphQL endpoint dapat diakses
    // ─────────────────────────────────────────────────────────
    public function test_graphql_endpoint_is_accessible(): void
    {
        Route::factory()->create();

        $response = $this->postJson('/graphql', [
            'query' => '{ routes { id route_code origin destination } }',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'routes',
                ],
            ]);
    }

    // ─────────────────────────────────────────────────────────
    // Kriteria 9: Query GraphQL (introspection) berhasil
    // ─────────────────────────────────────────────────────────
    public function test_graphql_introspection_works(): void
    {
        $response = $this->postJson('/graphql', [
            'query' => '{ __schema { queryType { name } types { name } } }',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '__schema' => [
                        'queryType',
                        'types',
                    ],
                ],
            ]);

        // Verifikasi type Route ada
        $types = collect($response->json('data.__schema.types'))
            ->pluck('name')
            ->toArray();

        $this->assertContains('Route', $types, 'GraphQL schema harus punya type Route');
    }
}
