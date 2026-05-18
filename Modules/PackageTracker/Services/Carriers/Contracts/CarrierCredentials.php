<?php

namespace Modules\PackageTracker\Services\Carriers\Contracts;

use Modules\PackageTracker\Models\Carrier;

class CarrierCredentials
{
    public function __construct(
        public readonly ?string $baseUrl,
        public readonly ?string $apiKey,
        public readonly ?string $apiSecret,
        public readonly array $settings = [],
    ) {}

    public static function fromCarrier(Carrier $carrier): self
    {
        $configured = config('package_tracker.carriers.' . $carrier->code, []);
        $settings = array_replace_recursive($configured['settings'] ?? [], $carrier->settings ?? []);

        return new self(
            baseUrl: $carrier->api_base_url ?: ($configured['base_url'] ?? null),
            apiKey: $carrier->api_key ?: ($configured['api_key'] ?? null),
            apiSecret: $carrier->api_secret ?: ($configured['api_secret'] ?? null),
            settings: $settings,
        );
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}
