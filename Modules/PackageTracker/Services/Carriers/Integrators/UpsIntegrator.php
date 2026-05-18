<?php

namespace Modules\PackageTracker\Services\Carriers\Integrators;

use Modules\PackageTracker\Services\Carriers\Contracts\AbstractCarrierIntegrator;
use Modules\PackageTracker\Services\Carriers\Drivers\UpsTrackingClient;

class UpsIntegrator extends AbstractCarrierIntegrator
{
    public function code(): string
    {
        return 'ups';
    }

    public function name(): string
    {
        return 'UPS Tracking';
    }

    public function clientClass(): string
    {
        return UpsTrackingClient::class;
    }

    public function defaultBaseUrl(): ?string
    {
        return env('PACKAGE_TRACKER_UPS_BASE_URL', 'https://onlinetools.ups.com');
    }

    public function supportsWebhooks(): bool
    {
        return true;
    }

    public function defaultSettings(): array
    {
        return ['locale' => env('PACKAGE_TRACKER_UPS_LOCALE', 'en_US'), 'transaction_src' => env('PACKAGE_TRACKER_UPS_TRANSACTION_SRC', 'LSGPackageTracker')];
    }

    public function credentialSchema(): array
    {
        return [
            ['key' => 'client_id', 'label' => 'Client Id', 'type' => 'password', 'required' => true],
            ['key' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password', 'required' => true],
            ['key' => 'access_token', 'label' => 'Access Token', 'type' => 'password', 'required' => false],
        ];
    }

    public function trackingNumberHints(): array
    {
        return ['Use exact tracking number as provided by the carrier.'];
    }
}
