<?php

namespace Modules\PackageTracker\Services\Carriers\Integrators;

use Modules\PackageTracker\Services\Carriers\Contracts\AbstractCarrierIntegrator;
use Modules\PackageTracker\Services\Carriers\Drivers\CttTrackingClient;

class CttIntegrator extends AbstractCarrierIntegrator
{
    public function code(): string
    {
        return 'ctt';
    }

    public function name(): string
    {
        return 'CTT';
    }

    public function clientClass(): string
    {
        return CttTrackingClient::class;
    }

    public function defaultBaseUrl(): ?string
    {
        return env('PACKAGE_TRACKER_CTT_BASE_URL');
    }

    public function supportsWebhooks(): bool
    {
        return false;
    }

    public function defaultSettings(): array
    {
        return ['tracking_path' => env('PACKAGE_TRACKER_CTT_TRACKING_PATH', 'tracking'), 'method' => env('PACKAGE_TRACKER_CTT_METHOD', 'GET')];
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
