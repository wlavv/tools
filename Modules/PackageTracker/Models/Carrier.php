<?php

namespace Modules\PackageTracker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrier extends Model
{
    use HasFactory;

    protected $table = 'package_tracker_carriers';

    protected $fillable = [
        'code', 'name', 'driver', 'api_base_url', 'api_key', 'api_secret',
        'is_active', 'supports_webhooks', 'settings', 'last_health_check_at', 'last_health_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'supports_webhooks' => 'boolean',
        'settings' => 'array',
        'last_health_check_at' => 'datetime',
    ];

    protected $hidden = ['api_key', 'api_secret'];

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'carrier_id');
    }
}
