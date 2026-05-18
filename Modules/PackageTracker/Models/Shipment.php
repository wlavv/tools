<?php

namespace Modules\PackageTracker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\PackageTracker\Enums\TrackingStatus;

class Shipment extends Model
{
    use HasFactory;

    protected $table = 'package_tracker_shipments';

    protected $fillable = [
        'carrier_id', 'tracking_number', 'public_token', 'public_tracking_enabled',
        'external_reference', 'store_code', 'order_reference', 'customer_email',
        'destination_country', 'status', 'substatus', 'last_location',
        'estimated_delivery_at', 'delivered_at', 'last_event_at', 'last_polled_at', 'next_poll_at',
        'sla_due_at', 'public_viewed_at', 'is_stale', 'has_exception', 'metadata', 'poll_attempts',
    ];

    protected $casts = [
        'estimated_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'last_event_at' => 'datetime',
        'last_polled_at' => 'datetime',
        'next_poll_at' => 'datetime',
        'sla_due_at' => 'datetime',
        'public_viewed_at' => 'datetime',
        'public_tracking_enabled' => 'boolean',
        'is_stale' => 'boolean',
        'has_exception' => 'boolean',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Shipment $shipment) {
            if (empty($shipment->public_token)) {
                $shipment->public_token = Str::random(48);
            }

            if ($shipment->public_tracking_enabled === null) {
                $shipment->public_tracking_enabled = true;
            }
        });
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(TrackingEvent::class, 'shipment_id')->orderByDesc('event_at');
    }

    public function statusEnum(): TrackingStatus
    {
        return TrackingStatus::tryFrom($this->status) ?? TrackingStatus::Unknown;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, TrackingStatus::terminalValues(), true);
    }

    public function publicUrl(): ?string
    {
        if (! $this->public_token || ! $this->public_tracking_enabled) {
            return null;
        }

        return route('package_tracker.public.show', $this->public_token);
    }
}
