<?php

namespace Modules\PackageTracker\Services\Carriers\Integrators;

use Modules\PackageTracker\Services\Carriers\Contracts\AbstractCarrierIntegrator;
use Modules\PackageTracker\Services\Carriers\Drivers\DpdTrackingClient;

class DpdIntegrator extends AbstractCarrierIntegrator
{
    public function code(): string
    {
        return 'dpd';
    }

    public function name(): string
    {
        return 'DPD';
    }

    public function clientClass(): string
    {
        return DpdTrackingClient::class;
    }

    public function defaultBaseUrl(): ?string
    {
        return env('PACKAGE_TRACKER_DPD_BASE_URL');
    }

    public function supportsWebhooks(): bool
    {
        return false;
    }

    public function defaultSettings(): array
    {
        return ['tracking_path' => env('PACKAGE_TRACKER_DPD_TRACKING_PATH', 'tracking'), 'tracking_param' => env('PACKAGE_TRACKER_DPD_TRACKING_PARAM', 'trackingNumber'), 'method' => env('PACKAGE_TRACKER_DPD_METHOD', 'GET')];
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
