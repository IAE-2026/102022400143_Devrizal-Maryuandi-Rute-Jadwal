<?php

namespace Database\Seeders;

use App\Models\Route;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        $seeds = [
            [
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
            ],
            [
                'id' => 'rte_002',
                'route_code' => 'BDG-JKT-002',
                'origin' => 'Bandung',
                'destination' => 'Jakarta',
                'departure_point' => 'Pool Buah Batu',
                'arrival_point' => 'Terminal Lebak Bulus',
                'departure_date' => '2026-05-21',
                'departure_time' => '10:00',
                'arrival_time' => '13:30',
                'vehicle_type' => 'Travel Regular',
                'price' => 120000,
                'seat_capacity' => 12,
                'available_seats' => 12,
                'status' => 'available',
            ],
            [
                'id' => 'rte_003',
                'route_code' => 'JKT-BDG-001',
                'origin' => 'Jakarta',
                'destination' => 'Bandung',
                'departure_point' => 'Terminal Lebak Bulus',
                'arrival_point' => 'Pool Dago',
                'departure_date' => '2026-05-22',
                'departure_time' => '14:00',
                'arrival_time' => '17:15',
                'vehicle_type' => 'Bus AKAP',
                'price' => 95000,
                'seat_capacity' => 40,
                'available_seats' => 0,
                'status' => 'full',
            ],
        ];

        foreach ($seeds as $row) {
            Route::updateOrCreate(['id' => $row['id']], $row);
        }
    }
}
