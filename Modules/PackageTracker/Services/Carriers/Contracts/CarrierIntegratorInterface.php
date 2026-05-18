<?php

namespace Modules\PackageTracker\Services\Carriers\Contracts;

interface CarrierIntegratorInterface
{
    public function code(): string;
    public function name(): string;
    public function clientClass(): string;
    public function defaultBaseUrl(): ?string;
    public function supportsWebhooks(): bool;
    public function capabilities(): array;
    public function defaultSettings(): array;
    public function credentialSchema(): array;
    public function trackingNumberHints(): array;
}
