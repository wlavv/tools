<?php

namespace Modules\PackageTracker\Services\Carriers\Drivers;

use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\Shipment;
use Modules\PackageTracker\Services\Carriers\CarrierTrackingResponse;
use Modules\PackageTracker\Services\Carriers\Contracts\CarrierCredentials;
use Modules\PackageTracker\Services\Carriers\Contracts\TrackingRequest;
use Modules\PackageTracker\Services\Carriers\Support\AbstractHttpCarrierClient;
use Modules\PackageTracker\Services\Carriers\Support\CarrierPayloadReader as Reader;

class InpostTrackingClient extends AbstractHttpCarrierClient
{
    protected function track(CarrierCredentials $credentials, TrackingRequest $request, Carrier $carrier, Shipment $shipment): CarrierTrackingResponse
    {
        $response = $this->http($credentials)
            ->withHeaders(array_filter([
                'Authorization' => $credentials->apiKey ? 'Bearer ' . $credentials->apiKey : null,
            ]))
            ->get($this->endpoint($credentials, 'v1/tracking/' . rawurlencode($request->trackingNumber)));

        $response->throw();
        $payload = $response->json() ?? [];
        $status = Reader::first($payload, ['status', 'tracking_details.0.status'], 'unknown');
        $events = [];

        foreach (Reader::list($payload, ['tracking_details', 'events']) as $event) {
            $date = Reader::first($event, ['datetime', 'created_at', 'date']);
            $rawStatus = Reader::first($event, ['status', 'description'], $status);
            $location = Reader::first($event, ['location', 'agency', 'origin_status']);

            $events[] = [
                'carrier_event_id' => $this->eventId('inpost', $request->trackingNumber, $date, $rawStatus, $location),
                'raw_status' => (string) $rawStatus,
                'description' => Reader::first($event, ['description', 'status']),
                'location' => $location,
                'event_at' => $date,
                'raw_payload' => $event,
            ];
        }

        return new CarrierTrackingResponse(
            status: (string) $status,
            substatus: Reader::first($payload, ['custom_attributes.target_machine_detail.name', 'service']),
            lastLocation: Reader::first($payload, ['tracking_details.0.location', 'custom_attributes.target_machine_detail.address.line1']),
            estimatedDeliveryAt: Reader::first($payload, ['estimated_delivery_date', 'expected_delivery_date']),
            deliveredAt: in_array(strtolower((string) $status), ['delivered', 'odebrana', 'collected_from_machine'], true) ? Reader::first($payload, ['updated_at', 'tracking_details.0.datetime']) : null,
            events: $events,
            raw: $payload,
        );
    }
}
