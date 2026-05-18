<?php

namespace Modules\PackageTracker\Models;

use Illuminate\Database\Eloquent\Model;

class ClientCarrierAccess extends Model
{
    protected $table = 'package_tracker_client_carriers';

    protected $fillable = [
        'client_key', 'carrier_code', 'is_enabled', 'credentials', 'settings', 'enabled_at', 'disabled_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'enabled_at' => 'datetime',
        'disabled_at' => 'datetime',
    ];
}
