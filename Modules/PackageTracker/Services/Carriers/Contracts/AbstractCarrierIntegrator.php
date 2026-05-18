<?php

namespace Modules\PackageTracker\Services\Carriers\Contracts;

abstract class AbstractCarrierIntegrator implements CarrierIntegratorInterface
{
    public function supportsWebhooks(): bool
    {
        return false;
    }

    public function capabilities(): array
    {
        return [CarrierCapability::Tracking->value];
    }

    public function defaultSettings(): array
    {
        return [];
    }

    public function credentialSchema(): array
    {
        return [];
    }

    public function trackingNumberHints(): array
    {
        return [];
    }
}
