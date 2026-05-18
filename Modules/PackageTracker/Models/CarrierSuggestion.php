<?php

namespace Modules\PackageTracker\Models;

use Illuminate\Database\Eloquent\Model;

class CarrierSuggestion extends Model
{
    protected $table = 'package_tracker_carrier_suggestions';

    protected $fillable = [
        'client_key', 'tracking_number', 'requested_carrier_code', 'suggested_carrier_code',
        'status', 'confidence', 'reason', 'raw_payload', 'resolved_at',
    ];

    protected $casts = [
        'confidence' => 'float',
        'raw_payload' => 'array',
        'resolved_at' => 'datetime',
    ];
}
