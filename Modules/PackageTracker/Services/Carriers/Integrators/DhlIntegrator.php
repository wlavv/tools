<?php

namespace Modules\PackageTracker\Services\Carriers\Integrators;

use Modules\PackageTracker\Services\Carriers\Contracts\AbstractCarrierIntegrator;
use Modules\PackageTracker\Services\Carriers\Drivers\DhlUnifiedTrackingClient;

class DhlIntegrator extends AbstractCarrierIntegrator
{
    public function code(): string
    {
        return 'dhl';
    }

    public function name(): string
    {
        return 'DHL Unified Tracking';
    }

    public function clientClass(): string
    {
        return DhlUnifiedTrackingClient::class;
    }

    public function defaultBaseUrl(): ?string
    {
        return env('PACKAGE_TRACKER_DHL_BASE_URL', 'https://api-eu.dhl.com');
    }

    public function supportsWebhooks(): bool
    {
        return true;
    }

    public function defaultSettings(): array
    {
        return ['requester_country_code' => env('PACKAGE_TRACKER_DHL_REQUESTER_COUNTRY_CODE', 'PT')];
    }

    public function credentialSchema(): array
    {
        return [['key' => 'api_key', 'label' => 'Api Key', 'type' => 'password', 'required' => true]];
    }

    public function trackingNumberHints(): array
    {
        return ['Use exact tracking number as provided by the carrier.'];
    }
}
