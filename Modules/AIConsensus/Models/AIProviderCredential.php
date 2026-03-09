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
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public function setApiKeyAttribute(?string $value): void
    {
        $this->attributes['api_key_encrypted'] = filled($value) ? Crypt::encryptString($value) : null;
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
}
