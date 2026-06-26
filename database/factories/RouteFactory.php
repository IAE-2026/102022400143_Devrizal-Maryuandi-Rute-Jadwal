<?php

namespace Database\Factories;

use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Route>
 */
class RouteFactory extends Factory
{
    protected $model = Route::class;

    public function definition(): array
    {
        return [
            'route_code' => strtoupper($this->faker->unique()->bothify('???-???-###')),
            'origin' => $this->faker->city(),
            'destination' => $this->faker->city(),
            'departure_point' => 'Pool ' . $this->faker->streetName(),
            'arrival_point' => 'Terminal ' . $this->faker->streetName(),
            'departure_date' => $this->faker->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
            'departure_time' => $this->faker->time('H:i'),
            'arrival_time' => $this->faker->time('H:i'),
            'vehicle_type' => $this->faker->randomElement(['Travel Executive', 'Travel Regular', 'Bus AKAP', 'Minibus']),
            'price' => $this->faker->numberBetween(50000, 300000),
            'seat_capacity' => 12,
            'available_seats' => $this->faker->numberBetween(0, 12),
            'status' => $this->faker->randomElement(['available', 'full', 'inactive']),
        ];
    }
}
