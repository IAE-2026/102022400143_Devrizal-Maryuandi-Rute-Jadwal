<?php

namespace Database\Seeders;

use App\Models\Route;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        Route::firstOrCreate(
            ['route_code' => 'BDG-JKT-001'],
            [
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
            ]
        );

        Route::firstOrCreate(
            ['route_code' => 'BDG-JKT-002'],
            [
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
            ]
        );
    }
}