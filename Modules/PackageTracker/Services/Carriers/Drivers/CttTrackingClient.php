<?php

namespace Modules\PackageTracker\Services\Carriers\Drivers;

use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\Shipment;
use Modules\PackageTracker\Services\Carriers\CarrierTrackingResponse;
use Modules\PackageTracker\Services\Carriers\Contracts\CarrierCredentials;
use Modules\PackageTracker\Services\Carriers\Contracts\TrackingRequest;
use Modules\PackageTracker\Services\Carriers\Support\AbstractHttpCarrierClient;
use Modules\PackageTracker\Services\Carriers\Support\CarrierPayloadReader as Reader;

class CttTrackingClient extends AbstractHttpCarrierClient
{
    protected function track(CarrierCredentials $credentials, TrackingRequest $request, Carrier $carrier, Shipment $shipment): CarrierTrackingResponse
    {
        $path = $credentials->setting('tracking_path', 'tracking/' . rawurlencode($request->trackingNumber));
        $method = strtoupper((string) $credentials->setting('method', 'GET'));

        $http = $this->http($credentials)->withHeaders(array_filter([
            'Authorization' => $credentials->apiKey ? 'Bearer ' . $credentials->apiKey : null,
            'X-API-Key' => $credentials->setting('x_api_key', $credentials->apiKey),
            'Accept-Language' => $request->language,
        ]));

        $response = $method === 'POST'
            ? $http->post($this->endpoint($credentials, $path), ['objectId' => $request->trackingNumber])
            : $http->get($this->endpoint($credentials, $path));

        $response->throw();
        $payload = $response->json() ?? [];
        $root = data_get($payload, 'objects.0', data_get($payload, 'data.0', $payload));
        $status = Reader::first($root, ['status', 'statusCode', 'currentStatus', 'lastEvent.status'], 'unknown');
        $events = [];

        foreach (Reader::list($root, ['events', 'history', 'trackingEvents']) as $event) {
            $date = Reader::first($event, ['eventDate', 'date', 'timestamp', 'dateTime']);
            $rawStatus = Reader::first($event, ['status', 'statusCode', 'description'], $status);
            $location = Reader::first($event, ['location', 'office', 'city', 'country']);
            $events[] = [
                'carrier_event_id' => $this->eventId('ctt', $request->trackingNumber, $date, $rawStatus, $location),
                'raw_status' => (string) $rawStatus,
                'description' => Reader::first($event, ['description', 'status']),
                'location' => $location,
                'event_at' => $date,
                'raw_payload' => $event,
            ];
        }

        return new CarrierTrackingResponse(
            status: (string) $status,
            substatus: Reader::first($root, ['subStatus', 'lastEvent.description']),
            lastLocation: Reader::first($root, ['events.0.location', 'lastEvent.location']),
            estimatedDeliveryAt: Reader::first($root, ['estimatedDeliveryDate', 'deliveryForecast']),
            deliveredAt: str_contains(strtolower((string) $status), 'entreg') || str_contains(strtolower((string) $status), 'deliver') ? Reader::first($root, ['deliveredAt', 'lastEvent.eventDate']) : null,
            events: $events,
            raw: $payload,
        );
    }
}
