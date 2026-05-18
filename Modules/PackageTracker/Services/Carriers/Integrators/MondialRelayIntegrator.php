<?php

namespace Modules\PackageTracker\Services\Carriers\Integrators;

use Modules\PackageTracker\Services\Carriers\Contracts\AbstractCarrierIntegrator;
use Modules\PackageTracker\Services\Carriers\Drivers\MondialRelayTrackingClient;

class MondialRelayIntegrator extends AbstractCarrierIntegrator
{
    public function code(): string
    {
        return 'mondial_relay';
    }

    public function name(): string
    {
        return 'Mondial Relay';
    }

    public function clientClass(): string
    {
        return MondialRelayTrackingClient::class;
    }

    public function defaultBaseUrl(): ?string
    {
        return env('PACKAGE_TRACKER_MONDIAL_RELAY_BASE_URL', 'https://api.mondialrelay.com');
    }

    public function supportsWebhooks(): bool
    {
        return false;
    }

    public function defaultSettings(): array
    {
        return [
            'country' => env('PACKAGE_TRACKER_MONDIAL_RELAY_COUNTRY', env('PACKAGE_TRACKER_DEFAULT_DESTINATION_COUNTRY', 'PT')),
            'language' => env('PACKAGE_TRACKER_MONDIAL_RELAY_LANGUAGE', 'PT'),
        ];
    }

    public function credentialSchema(): array
    {
        return [['key' => 'enseigne', 'label' => 'Enseigne', 'type' => 'password', 'required' => true], ['key' => 'private_key', 'label' => 'Private Key', 'type' => 'password', 'required' => true]];
    }

    public function trackingNumberHints(): array
    {
        return ['Use exact tracking number as provided by the carrier.'];
    }
}
