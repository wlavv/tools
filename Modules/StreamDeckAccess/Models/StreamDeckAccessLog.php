<?php

namespace Modules\StreamDeckAccess\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamDeckAccessLog extends Model
{
    protected $table = 'streamdeck_access_logs';

    protected $guarded = [];

    protected $fillable = [];

    protected $casts = [
        'payload_snapshot' => 'array',
        'response' => 'array',
        'response_ms' => 'integer',
    ];

    public function accessPoint(): BelongsTo
    {
        return $this->belongsTo(StreamDeckAccessPoint::class, 'streamdeck_access_point_id');
    }
}
