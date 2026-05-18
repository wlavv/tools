<?php

namespace Modules\PackageTracker\Services\Carriers;

use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\Shipment;

class ManualCarrierClient implements CarrierClientInterface
{
    public function fetchTracking(Carrier $carrier, Shipment $shipment): CarrierTrackingResponse
    {
        return new CarrierTrackingResponse(
            status: $shipment->status ?: 'pending',
            substatus: $shipment->substatus,
            lastLocation: $shipment->last_location,
            estimatedDeliveryAt: optional($shipment->estimated_delivery_at)->toISOString(),
            deliveredAt: optional($shipment->delivered_at)->toISOString(),
            events: [],
            raw: ['source' => 'manual']
        );
    }

    public function healthCheck(Carrier $carrier): bool
    {
        return true;
    }
}
