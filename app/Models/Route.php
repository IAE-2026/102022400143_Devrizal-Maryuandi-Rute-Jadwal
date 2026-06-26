<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
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

    protected $casts = [
        'departure_date' => 'date',
        'price' => 'integer',
        'seat_capacity' => 'integer',
        'available_seats' => 'integer',
    ];
}
