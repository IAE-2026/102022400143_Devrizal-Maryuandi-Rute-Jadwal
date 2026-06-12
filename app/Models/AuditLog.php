<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'booking_reference',
        'route_id',
        'reserved_seats',
        'action',
        'receipt_number',
        'audit_status',
        'request_payload',
        'response_payload',
    ];
}
