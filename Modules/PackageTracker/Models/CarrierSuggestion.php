<?php

namespace Modules\PackageTracker\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\PackageTracker\Support\JsonSanitizer;

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

    public function setRawPayloadAttribute(mixed $value): void
    {
        $this->attributes['raw_payload'] = JsonSanitizer::encode($value);
    }
}
