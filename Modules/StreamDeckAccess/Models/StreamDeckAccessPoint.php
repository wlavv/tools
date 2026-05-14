<?php

namespace Modules\StreamDeckAccess\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\StreamDeckAccess\Support\TokenFactory;

class StreamDeckAccessPoint extends Model
{
    use SoftDeletes;

    protected $table = 'streamdeck_access_points';

    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
        'payload' => 'array',
        'allowed_ips' => 'array',
        'expires_at' => 'datetime',
        'max_uses' => 'integer',
        'use_count' => 'integer',
        'cooldown_seconds' => 'integer',
        'last_used_at' => 'datetime',
        'respond_json' => 'boolean',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(StreamDeckAccessLog::class, 'streamdeck_access_point_id');
    }

    public function tokenMatches(string $plainToken): bool
    {
        return hash_equals((string) $this->token_hash, TokenFactory::hash($plainToken));
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }
}
