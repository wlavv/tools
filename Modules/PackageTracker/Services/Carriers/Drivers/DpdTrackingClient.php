<?php

namespace Modules\PackageTracker\Services\Carriers\Drivers;

use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\Shipment;
use Modules\PackageTracker\Services\Carriers\CarrierTrackingResponse;
use Modules\PackageTracker\Services\Carriers\Contracts\CarrierCredentials;
use Modules\PackageTracker\Services\Carriers\Contracts\TrackingRequest;
use Modules\PackageTracker\Services\Carriers\Support\AbstractHttpCarrierClient;
use Modules\PackageTracker\Services\Carriers\Support\CarrierPayloadReader as Reader;

class DpdTrackingClient extends AbstractHttpCarrierClient
{
    protected function track(CarrierCredentials $credentials, TrackingRequest $request, Carrier $carrier, Shipment $shipment): CarrierTrackingResponse
    {
        $path = $credentials->setting('tracking_path', 'tracking');
        $method = strtoupper((string) $credentials->setting('method', 'GET'));
        $trackingParam = $credentials->setting('tracking_param', 'trackingNumber');

        $http = $this->http($credentials)->withHeaders(array_filter([
            'Authorization' => $credentials->apiKey ? 'Bearer ' . $credentials->apiKey : null,
            'X-API-Key' => $credentials->setting('x_api_key', $credentials->apiKey),
        ]));

        $response = $method === 'POST'
            ? $http->post($this->endpoint($credentials, $path), array_filter([$trackingParam => $request->trackingNumber]))
            : $http->get($this->endpoint($credentials, $path), array_filter([$trackingParam => $request->trackingNumber]));

        $response->throw();
        $payload = $response->json() ?? [];
        $root = data_get($payload, 'data.0', data_get($payload, 'shipment', $payload));
        $status = Reader::first($root, ['status', 'statusCode', 'parcelStatus', 'currentStatus.description'], 'unknown');
        $events = [];

        foreach (Reader::list($root, ['events', 'trackingEvents', 'history', 'scans']) as $event) {
            $date = Reader::first($event, ['dateTime', 'timestamp', 'date', 'eventDate']);
            $rawStatus = Reader::first($event, ['status', 'statusCode', 'description', 'event'], $status);
            $location = Reader::first($event, ['location', 'depot', 'city', 'country']);
            $events[] = [
                'carrier_event_id' => $this->eventId('dpd', $request->trackingNumber, $date, $rawStatus, $location),
                'raw_status' => (string) $rawStatus,
                'description' => Reader::first($event, ['description', 'event', 'status']),
                'location' => $location,
                'event_at' => $date,
                'raw_payload' => $event,
            ];
        }

        return new CarrierTrackingResponse(
            status: (string) $status,
            substatus: Reader::first($root, ['substatus', 'currentStatus.description']),
            lastLocation: Reader::first($root, ['events.0.location', 'trackingEvents.0.location', 'depot']),
            estimatedDeliveryAt: Reader::first($root, ['estimatedDeliveryDate', 'eta', 'deliveryDate']),
            deliveredAt: str_contains(strtolower((string) $status), 'deliver') ? Reader::first($root, ['deliveredAt', 'events.0.dateTime']) : null,
            events: $events,
            raw: $payload,
        );
    }
}
