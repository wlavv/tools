<?php

namespace Modules\PackageTracker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PackageTrackerClient extends Model
{
    protected $table = 'package_tracker_clients';

    protected $fillable = [
        'client_key', 'name', 'contact_email', 'public_token', 'is_active', 'theme', 'last_viewed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'theme' => 'array',
        'last_viewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PackageTrackerClient $client) {
            if (empty($client->client_key)) {
                $client->client_key = self::makeClientKey($client->name);
            }

            if (empty($client->public_token)) {
                $client->public_token = Str::random(48);
            }
        });
    }

    public static function makeClientKey(string $name): string
    {
        $base = Str::slug($name) ?: 'client';
        $key = $base;
        $suffix = 2;

        while (self::query()->where('client_key', $key)->exists()) {
            $key = $base . '-' . $suffix++;
        }

        return $key;
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'client_key', 'client_key')->latest();
    }

    public function carrierAccesses(): HasMany
    {
        return $this->hasMany(ClientCarrierAccess::class, 'client_key', 'client_key');
    }

    public function publicUrl(): ?string
    {
        if (! $this->public_token || ! $this->is_active) {
            return null;
        }

        return route('package_tracker.public.client', $this->public_token);
    }
}
