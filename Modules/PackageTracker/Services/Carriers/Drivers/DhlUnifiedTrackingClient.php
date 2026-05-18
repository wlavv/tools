<?php

namespace Modules\PackageTracker\Services\Carriers\Drivers;

use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\Shipment;
use Modules\PackageTracker\Services\Carriers\CarrierTrackingResponse;
use Modules\PackageTracker\Services\Carriers\Contracts\CarrierCredentials;
use Modules\PackageTracker\Services\Carriers\Contracts\TrackingRequest;
use Modules\PackageTracker\Services\Carriers\Support\AbstractHttpCarrierClient;
use Modules\PackageTracker\Services\Carriers\Support\CarrierPayloadReader as Reader;

class DhlUnifiedTrackingClient extends AbstractHttpCarrierClient
{
    protected function track(CarrierCredentials $credentials, TrackingRequest $request, Carrier $carrier, Shipment $shipment): CarrierTrackingResponse
    {
        $response = $this->http($credentials)
            ->withHeaders(array_filter([
                'DHL-API-Key' => $credentials->apiKey,
            ]))
            ->get($this->endpoint($credentials, 'track/shipments'), array_filter([
                'trackingNumber' => $request->trackingNumber,
                'service' => $credentials->setting('service'),
                'requesterCountryCode' => $credentials->setting('requester_country_code', $request->destinationCountry),
                'originCountryCode' => $credentials->setting('origin_country_code'),
                'recipientPostalCode' => $request->postalCode,
                'language' => $request->language,
            ]));

        $response->throw();
        $payload = $response->json() ?? [];
        $shipmentData = data_get($payload, 'shipments.0', []);
        $status = Reader::first($shipmentData, ['status.statusCode', 'status.status', 'status.description'], 'unknown');
        $events = [];

        foreach (Reader::list($shipmentData, ['events']) as $event) {
            $date = Reader::first($event, ['timestamp', 'date', 'eventTime']);
            $rawStatus = Reader::first($event, ['statusCode', 'status', 'description'], $status);
            $location = Reader::first($event, ['location.address.addressLocality', 'location.address.countryCode', 'location.locationName']);

            $events[] = [
                'carrier_event_id' => $this->eventId('dhl', $request->trackingNumber, $date, $rawStatus, $location),
                'raw_status' => (string) $rawStatus,
                'description' => Reader::first($event, ['description', 'status']),
                'location' => $location,
                'event_at' => $date,
                'raw_payload' => $event,
            ];
        }

        return new CarrierTrackingResponse(
            status: (string) $status,
            substatus: Reader::first($shipmentData, ['status.description']),
            lastLocation: Reader::first($shipmentData, ['events.0.location.address.addressLocality', 'events.0.location.locationName']),
            estimatedDeliveryAt: Reader::first($shipmentData, ['estimatedTimeOfDelivery', 'details.estimatedDeliveryDate']),
            deliveredAt: strtolower((string) $status) === 'delivered' ? Reader::first($shipmentData, ['status.timestamp', 'events.0.timestamp']) : null,
            events: $events,
            raw: $payload,
        );
    }
}
