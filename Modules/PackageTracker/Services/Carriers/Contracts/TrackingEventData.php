<?php

namespace Modules\PackageTracker\Services\Carriers\Contracts;

class TrackingEventData
{
    public function __construct(
        public readonly ?string $carrierEventId,
        public readonly string $rawStatus,
        public readonly ?string $normalizedStatus = null,
        public readonly ?string $substatus = null,
        public readonly ?string $description = null,
        public readonly ?string $location = null,
        public readonly ?string $eventAt = null,
        public readonly array $rawPayload = [],
    ) {}

    public function toArray(): array
    {
        return [
            'carrier_event_id' => $this->carrierEventId,
            'raw_status' => $this->rawStatus,
            'normalized_status' => $this->normalizedStatus,
            'substatus' => $this->substatus,
            'description' => $this->description,
            'location' => $this->location,
            'event_at' => $this->eventAt,
            'raw_payload' => $this->rawPayload,
        ];
    }
}
