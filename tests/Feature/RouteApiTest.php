<?php

namespace Tests\Feature;

use App\Models\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteApiTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = '102022400143';

    private function withKey(): array
    {
        return ['X-IAE-KEY' => self::KEY, 'Accept' => 'application/json'];
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'route_code' => 'BDG-JKT-099',
            'origin' => 'Bandung',
            'destination' => 'Jakarta',
            'departure_point' => 'Pool Dago',
            'arrival_point' => 'Terminal Lebak Bulus',
            'departure_date' => '2026-06-01',
            'departure_time' => '09:00',
            'arrival_time' => '12:30',
            'vehicle_type' => 'Travel Executive',
            'price' => 150000,
            'seat_capacity' => 12,
            'available_seats' => 12,
            'status' => 'available',
        ], $overrides);
    }

    public function test_request_tanpa_key_ditolak_401(): void
    {
        $response = $this->getJson('/api/v1/routes');

        $response->assertStatus(401)
            ->assertJson(['status' => 'error']);
    }

    public function test_request_dengan_key_salah_ditolak_403(): void
    {
        $response = $this->getJson('/api/v1/routes', ['X-IAE-KEY' => 'salah']);

        $response->assertStatus(403)
            ->assertJson(['status' => 'error']);
    }

    public function test_get_list_mengembalikan_200_dengan_wrapper(): void
    {
        Route::create([
            'id' => 'rte_001',
            'route_code' => 'BDG-JKT-001',
            'origin' => 'Bandung',
            'destination' => 'Jakarta',
            'departure_point' => 'Pool Pasteur',
            'arrival_point' => 'Terminal Kampung Rambutan',
            'departure_date' => '2026-05-20',
            'departure_time' => '08:00',
            'arrival_time' => '11:30',
            'vehicle_type' => 'Travel Executive',
            'price' => 150000,
            'seat_capacity' => 12,
            'available_seats' => 8,
            'status' => 'available',
        ]);

        $response = $this->getJson('/api/v1/routes', $this->withKey());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [['id', 'route_code', 'origin', 'destination', 'status']],
                'meta' => ['service_name', 'api_version'],
            ])
            ->assertJsonPath('status', 'success');
    }

    public function test_get_detail_tidak_ditemukan_mengembalikan_404_wrapper(): void
    {
        $response = $this->getJson('/api/v1/routes/rte_999', $this->withKey());

        $response->assertStatus(404)
            ->assertJsonStructure(['status', 'message', 'errors'])
            ->assertJsonPath('status', 'error');
    }

    public function test_post_membuat_rute_mengembalikan_201_wrapper(): void
    {
        $response = $this->postJson('/api/v1/routes', $this->validPayload(), $this->withKey());

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.route_code', 'BDG-JKT-099')
            ->assertJsonPath('data.status', 'available');

        $this->assertDatabaseCount('routes', 1);
    }

    public function test_post_tanpa_field_wajib_mengembalikan_422_wrapper(): void
    {
        $response = $this->postJson('/api/v1/routes', [], $this->withKey());

        $response->assertStatus(422)
            ->assertJsonStructure(['status', 'message', 'errors'])
            ->assertJsonPath('status', 'error');
    }

    public function test_post_kursi_melebihi_kapasitas_mengembalikan_422(): void
    {
        $response = $this->postJson('/api/v1/routes', $this->validPayload([
            'seat_capacity' => 10,
            'available_seats' => 20,
        ]), $this->withKey());

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function test_path_tidak_dikenal_mengembalikan_404_bukan_405(): void
    {
        $response = $this->getJson('/api/v1/tidak-ada', $this->withKey());

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error');
    }

    public function test_method_tidak_diizinkan_mengembalikan_405_wrapper(): void
    {
        // DELETE tidak terdaftar untuk endpoint ini.
        $response = $this->json('DELETE', '/api/v1/routes', [], $this->withKey());

        $response->assertStatus(405)
            ->assertJsonPath('status', 'error');
    }
}
