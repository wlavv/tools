<?php

namespace Modules\PackageTracker\Services\Carriers;

use Carbon\Carbon;
use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\Shipment;

class MockCarrierClient implements CarrierClientInterface
{
    public function fetchTracking(Carrier $carrier, Shipment $shipment): CarrierTrackingResponse
    {
        $sequence = ['label_created', 'collected', 'in_transit', 'out_for_delivery', 'delivered'];
        $currentIndex = max(0, array_search($shipment->status, $sequence, true));
        $nextStatus = $sequence[min($currentIndex + 1, count($sequence) - 1)];
        $now = Carbon::now();

        return new CarrierTrackingResponse(
            status: $nextStatus,
            substatus: $nextStatus === 'delivered' ? 'signed' : null,
            lastLocation: 'Mock Hub Lisbon',
            estimatedDeliveryAt: $now->copy()->addDay()->toISOString(),
            deliveredAt: $nextStatus === 'delivered' ? $now->toISOString() : null,
            events: [[
                'carrier_event_id' => 'mock-' . $shipment->id . '-' . $now->timestamp,
                'raw_status' => strtoupper($nextStatus),
                'normalized_status' => $nextStatus,
                'substatus' => $nextStatus === 'delivered' ? 'signed' : null,
                'description' => 'Mock event generated for testing.',
                'location' => 'Mock Hub Lisbon',
                'event_at' => $now->toDateTimeString(),
                'raw_payload' => ['mock' => true],
            ]],
            raw: ['mock' => true]
        );
    }

    public function healthCheck(Carrier $carrier): bool
    {
        return true;
    }
}
