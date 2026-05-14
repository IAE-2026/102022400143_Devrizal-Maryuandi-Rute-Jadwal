<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
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
        'status',
    ];
}