<?php

namespace Modules\PackageTracker\Services\Carriers\Drivers;

use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\Shipment;
use Modules\PackageTracker\Services\Carriers\CarrierTrackingResponse;
use Modules\PackageTracker\Services\Carriers\Contracts\CarrierCredentials;
use Modules\PackageTracker\Services\Carriers\Contracts\TrackingRequest;
use Modules\PackageTracker\Services\Carriers\Support\AbstractHttpCarrierClient;
use Modules\PackageTracker\Services\Carriers\Support\CarrierPayloadReader as Reader;

class TrackingMoreClient extends AbstractHttpCarrierClient
{
    protected function track(CarrierCredentials $credentials, TrackingRequest $request, Carrier $carrier, Shipment $shipment): CarrierTrackingResponse
    {
        $courierCode = (string) $credentials->setting('courier_code', $request->metadata['courier_code'] ?? $carrier->code);
        $payload = [
            'tracking_number' => $request->trackingNumber,
            'courier_code' => $courierCode,
            'destination_code' => $request->destinationCountry,
            'order_number' => $request->orderReference,
        ];

        $response = $this->http($credentials)
            ->withHeaders(array_filter([
                'Tracking-Api-Key' => $credentials->apiKey,
            ]))
            ->post($this->endpoint($credentials, 'v4/trackings/create'), array_filter($payload));

        $response->throw();
        $body = $response->json() ?? [];
        $root = data_get($body, 'data', $body);
        $status = Reader::first($root, ['delivery_status', 'status', 'substatus'], 'unknown');
        $events = [];

        foreach (Reader::list($root, ['origin_info.trackinfo', 'destination_info.trackinfo', 'tracking_info', 'events']) as $event) {
            $date = Reader::first($event, ['checkpoint_date', 'Date', 'time', 'date']);
            $rawStatus = Reader::first($event, ['checkpoint_status', 'StatusDescription', 'description', 'status'], $status);
            $location = Reader::first($event, ['location', 'Details', 'city', 'country_iso2']);

            $events[] = [
                'carrier_event_id' => $this->eventId('trackingmore_' . $courierCode, $request->trackingNumber, $date, $rawStatus, $location),
                'raw_status' => (string) $rawStatus,
                'description' => Reader::first($event, ['checkpoint_delivery_status', 'checkpoint_status', 'description', 'status']),
                'location' => $location,
                'event_at' => $date,
                'raw_payload' => $event,
            ];
        }

        return new CarrierTrackingResponse(
            status: (string) $status,
            substatus: Reader::first($root, ['substatus', 'delivery_status']),
            lastLocation: Reader::first($root, ['origin_info.trackinfo.0.location', 'destination_info.trackinfo.0.location']),
            estimatedDeliveryAt: Reader::first($root, ['scheduled_delivery_date', 'estimated_delivery_date']),
            deliveredAt: Reader::first($root, ['delivery_date']),
            events: $events,
            raw: $body,
        );
    }
}
