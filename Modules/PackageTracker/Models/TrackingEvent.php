<?php

namespace Modules\PackageTracker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\PackageTracker\Support\JsonSanitizer;

class TrackingEvent extends Model
{
    use HasFactory;

    protected $table = 'package_tracker_events';

    protected $fillable = [
        'shipment_id', 'carrier_id', 'carrier_event_id', 'raw_status', 'normalized_status',
        'substatus', 'description', 'location', 'event_at', 'raw_payload',
    ];

    protected $casts = [
        'event_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }

    public function setRawPayloadAttribute(mixed $value): void
    {
        $this->attributes['raw_payload'] = JsonSanitizer::encode($value);
    }
}
