<?php

namespace Modules\PackageTracker\Services\Carriers\Contracts;

use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\Shipment;

class TrackingRequest
{
    public function __construct(
        public readonly string $trackingNumber,
        public readonly ?string $carrierCode = null,
        public readonly ?string $destinationCountry = null,
        public readonly ?string $postalCode = null,
        public readonly ?string $orderReference = null,
        public readonly ?string $language = null,
        public readonly array $metadata = [],
    ) {}

    public static function fromShipment(Carrier $carrier, Shipment $shipment): self
    {
        $metadata = $shipment->metadata ?? [];

        return new self(
            trackingNumber: $shipment->tracking_number,
            carrierCode: $carrier->code,
            destinationCountry: $shipment->destination_country,
            postalCode: $metadata['destination_postal_code'] ?? $metadata['postal_code'] ?? null,
            orderReference: $shipment->order_reference,
            language: $metadata['language'] ?? config('app.locale'),
            metadata: $metadata,
        );
    }

    public function toArray(): array
    {
        return [
            'tracking_number' => $this->trackingNumber,
            'carrier_code' => $this->carrierCode,
            'destination_country' => $this->destinationCountry,
            'postal_code' => $this->postalCode,
            'order_reference' => $this->orderReference,
            'language' => $this->language,
            'metadata' => $this->metadata,
        ];
    }
}
