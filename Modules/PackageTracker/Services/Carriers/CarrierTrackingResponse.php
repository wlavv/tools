<?php

namespace Modules\PackageTracker\Services\Carriers;

class CarrierTrackingResponse
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $substatus = null,
        public readonly ?string $lastLocation = null,
        public readonly ?string $estimatedDeliveryAt = null,
        public readonly ?string $deliveredAt = null,
        public readonly array $events = [],
        public readonly array $raw = [],
    ) {}
}
