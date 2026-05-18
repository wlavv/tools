<?php

namespace Modules\PackageTracker\Services\Carriers\Integrators;

use Modules\PackageTracker\Services\Carriers\Contracts\AbstractCarrierIntegrator;
use Modules\PackageTracker\Services\Carriers\Drivers\InpostTrackingClient;

class InpostIntegrator extends AbstractCarrierIntegrator
{
    public function code(): string
    {
        return 'inpost';
    }

    public function name(): string
    {
        return 'InPost';
    }

    public function clientClass(): string
    {
        return InpostTrackingClient::class;
    }

    public function defaultBaseUrl(): ?string
    {
        return env('PACKAGE_TRACKER_INPOST_BASE_URL', 'https://api-shipx-pl.easypack24.net');
    }

    public function supportsWebhooks(): bool
    {
        return true;
    }

    public function defaultSettings(): array
    {
        return [
            'country' => env('PACKAGE_TRACKER_INPOST_COUNTRY', env('PACKAGE_TRACKER_DEFAULT_DESTINATION_COUNTRY', 'PT')),
            'language' => env('PACKAGE_TRACKER_INPOST_LANGUAGE', env('APP_LOCALE', 'pt')),
        ];
    }

    public function credentialSchema(): array
    {
        return [['key' => 'token', 'label' => 'Token', 'type' => 'password', 'required' => true]];
    }

    public function trackingNumberHints(): array
    {
        return ['Use exact tracking number as provided by the carrier.'];
    }
}
