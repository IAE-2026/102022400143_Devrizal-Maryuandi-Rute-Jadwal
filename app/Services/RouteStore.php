<?php

namespace App\Services;

use App\Models\Route;

class RouteStore
{
    public function all(): array
    {
        return Route::orderBy('created_at')->get()->map(fn (Route $r) => $this->toArray($r))->all();
    }

    public function find(string $id): ?array
    {
        $route = Route::find($id);

        return $route ? $this->toArray($route) : null;
    }

    public function findByCode(string $code): ?array
    {
        $route = Route::where('route_code', $code)->first();

        return $route ? $this->toArray($route) : null;
    }

    public function create(array $attributes): array
    {
        $attributes['id'] = $attributes['id'] ?? $this->nextId();

        $route = Route::create($attributes);

        return $this->toArray($route);
    }

    public function update(string $id, array $attributes): ?array
    {
        $route = Route::find($id);

        if ($route === null) {
            return null;
        }

        $route->fill($attributes);
        $route->save();

        return $this->toArray($route);
    }

    public function nextId(): string
    {
        $max = 0;

        foreach (Route::pluck('id') as $id) {
            $number = (int) preg_replace('/\D+/', '', (string) $id);
            $max = max($max, $number);
        }

        return 'rte_'.str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    private function toArray(Route $route): array
    {
        return [
            'id' => $route->id,
            'route_code' => $route->route_code,
            'origin' => $route->origin,
            'destination' => $route->destination,
            'departure_point' => $route->departure_point,
            'arrival_point' => $route->arrival_point,
            'departure_date' => optional($route->departure_date)->toDateString(),
            'departure_time' => $route->departure_time,
            'arrival_time' => $route->arrival_time,
            'vehicle_type' => $route->vehicle_type,
            'price' => $route->price !== null ? (int) $route->price : null,
            'seat_capacity' => $route->seat_capacity !== null ? (int) $route->seat_capacity : null,
            'available_seats' => $route->available_seats !== null ? (int) $route->available_seats : null,
            'status' => $route->status,
            'created_at' => optional($route->created_at)->toISOString(),
            'updated_at' => optional($route->updated_at)->toISOString(),
        ];
    }
}
