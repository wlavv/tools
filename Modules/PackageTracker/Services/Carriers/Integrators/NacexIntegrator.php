<?php

namespace Modules\PackageTracker\Services\Carriers\Integrators;

use Modules\PackageTracker\Services\Carriers\Contracts\AbstractCarrierIntegrator;
use Modules\PackageTracker\Services\Carriers\Drivers\NacexTrackingClient;

class NacexIntegrator extends AbstractCarrierIntegrator
{
    public function code(): string
    {
        return 'nacex';
    }

    public function name(): string
    {
        return 'NACEX';
    }

    public function clientClass(): string
    {
        return NacexTrackingClient::class;
    }

    public function defaultBaseUrl(): ?string
    {
        return env('PACKAGE_TRACKER_NACEX_BASE_URL', 'https://pda.nacex.com/nacex_ws');
    }

    public function supportsWebhooks(): bool
    {
        return false;
    }

    public function defaultSettings(): array
    {
        return ['tracking_path' => env('PACKAGE_TRACKER_NACEX_TRACKING_PATH', 'ws'), 'method_name' => env('PACKAGE_TRACKER_NACEX_METHOD_NAME', 'getEstadoEnvio')];
    }

    public function credentialSchema(): array
    {
        return [['key' => 'user', 'label' => 'User', 'type' => 'password', 'required' => true], ['key' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true]];
    }

    public function trackingNumberHints(): array
    {
        return ['Use exact tracking number as provided by the carrier.'];
    }
}
