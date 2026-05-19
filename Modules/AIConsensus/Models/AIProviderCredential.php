<?php

namespace Modules\AIConsensus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AIProviderCredential extends Model
{
    protected $table = 'ai_provider_credentials';

    protected $fillable = [
        'provider',
        'label',
        'api_key_encrypted',
        'base_url',
        'default_model',
        'is_active',
        'enabled',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'enabled' => 'boolean',
        'meta' => 'array',
    ];

    public function setApiKeyAttribute(?string $value): void
    {
        $this->attributes['api_key_encrypted'] = filled($value)
            ? Crypt::encryptString($value)
            : null;
    }

    public function getApiKeyAttribute(): ?string
    {
        if (empty($this->attributes['api_key_encrypted'])) {
            return null;
        }

        try {
            return Crypt::decryptString($this->attributes['api_key_encrypted']);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getIsActiveAttribute(): bool
    {
        return (bool) ($this->attributes['is_active'] ?? $this->attributes['enabled'] ?? false);
    }

    public function setIsActiveAttribute(bool $value): void
    {
        $this->attributes['is_active'] = $value ? 1 : 0;
    }
}
