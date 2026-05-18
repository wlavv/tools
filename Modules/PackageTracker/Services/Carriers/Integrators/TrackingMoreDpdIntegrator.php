<?php

namespace Modules\PackageTracker\Services\Carriers\Integrators;

use Modules\PackageTracker\Services\Carriers\Contracts\AbstractCarrierIntegrator;
use Modules\PackageTracker\Services\Carriers\Drivers\TrackingMoreClient;

class TrackingMoreDpdIntegrator extends AbstractCarrierIntegrator
{
    public function code(): string
    {
        return 'trackingmore_dpd';
    }

    public function name(): string
    {
        return 'TrackingMore DPD';
    }

    public function clientClass(): string
    {
        return TrackingMoreClient::class;
    }

    public function defaultBaseUrl(): ?string
    {
        return env('PACKAGE_TRACKER_TRACKINGMORE_BASE_URL', 'https://api.trackingmore.com');
    }

    public function supportsWebhooks(): bool
    {
        return true;
    }

    public function defaultSettings(): array
    {
        return [
            'courier_code' => env('PACKAGE_TRACKER_TRACKINGMORE_DPD_COURIER_CODE', 'dpd'),
        ];
    }

    public function credentialSchema(): array
    {
        return [
            ['key' => 'api_key', 'label' => 'TrackingMore API Key', 'type' => 'password', 'required' => true],
        ];
    }

    public function trackingNumberHints(): array
    {
        return ['Use the public DPD tracking number; TrackingMore resolves the DPD network by courier_code.'];
    }
}
