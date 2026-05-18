<?php

namespace Modules\PackageTracker\Services\Carriers\Drivers;

use InvalidArgumentException;
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
        if (! $credentials->baseUrl) {
            throw new InvalidArgumentException('DPD API base URL missing. Configure api_base_url on the DPD carrier or PACKAGE_TRACKER_DPD_BASE_URL.');
        }

        if (in_array(parse_url((string) $credentials->baseUrl, PHP_URL_HOST), ['api-test.dpd.com', 'www.api-test.dpd.com'], true)) {
            throw new InvalidArgumentException('DPD API base URL is invalid: api-test.dpd.com does not resolve. Configure the official DPD endpoint for the contracted account/country.');
        }

        if ($credentials->setting('api_type') === 'nst_shipment') {
            return $this->trackNstShipment($credentials, $request);
        }

        $path = $credentials->setting('tracking_path', 'tracking');
        $method = strtoupper((string) $credentials->setting('method', 'GET'));
        $trackingParam = $credentials->setting('tracking_param', 'trackingNumber');
        $query = array_filter([
            $trackingParam => $request->trackingNumber,
            'detail' => $credentials->setting('detail'),
            'show_all' => $credentials->setting('show_all'),
            'lang' => $credentials->setting('lang', $request->language),
        ], fn ($value) => $value !== null && $value !== '');

        $http = $this->http($credentials)->withHeaders(array_filter([
            'Authorization' => $credentials->apiKey ? 'Bearer ' . $credentials->apiKey : null,
            'X-API-Key' => $credentials->setting('x_api_key', $credentials->apiKey),
        ]));

        $response = $method === 'POST'
            ? $http->post($this->endpoint($credentials, $path), $query)
            : $http->get($this->endpoint($credentials, $path), $query);

        $response->throw();
        $payload = $response->json() ?? [];
        $root = data_get($payload, 'parcels.0', data_get($payload, 'data.0', data_get($payload, 'shipment', $payload)));
        $status = Reader::first($root, ['status', 'statusCode', 'parcelStatus', 'currentStatus.description'], 'unknown');
        $events = [];

        foreach (Reader::list($root, ['events', 'trackingEvents', 'history', 'scans', 'statuses']) as $event) {
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

    private function trackNstShipment(CarrierCredentials $credentials, TrackingRequest $request): CarrierTrackingResponse
    {
        $shipmentId = $request->metadata['dpd_shipment_id']
            ?? $request->metadata['shipment_id']
            ?? null;

        if (! $shipmentId && ctype_digit($request->trackingNumber)) {
            $shipmentId = $request->trackingNumber;
        }

        if (! $shipmentId) {
            throw new InvalidArgumentException('DPD NST Shipment API requires the internal shipmentId. Store it in shipment metadata as dpd_shipment_id; parcel/tracking number alone is not enough for this endpoint.');
        }

        $response = $this->http($credentials)
            ->withHeaders(array_filter([
                'Authorization' => $credentials->apiKey ? 'Bearer ' . $credentials->apiKey : null,
                'X-API-Key' => $credentials->setting('x_api_key', $credentials->apiKey),
            ]))
            ->get($this->endpoint($credentials, 'shipments/' . rawurlencode((string) $shipmentId)));

        $response->throw();

        $payload = $response->json() ?? [];
        $root = data_get($payload, 'shipment', $payload);
        $status = Reader::first($root, ['status', 'statusCode', 'state'], 'unknown');

        return new CarrierTrackingResponse(
            status: (string) $status,
            substatus: Reader::first($root, ['statusDescription', 'service.serviceAlias', 'service.name']),
            lastLocation: Reader::first($root, ['receiver.address.city', 'receiver.country', 'deliveryAddress.city']),
            estimatedDeliveryAt: Reader::first($root, ['plannedDeliveryDate', 'deliveryDate', 'pickup.plannedDate']),
            deliveredAt: str_contains(strtolower((string) $status), 'deliver') ? Reader::first($root, ['deliveredAt', 'deliveryDate']) : null,
            events: [],
            raw: $payload,
        );
    }
}
