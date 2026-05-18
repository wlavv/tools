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

class InpostTrackingClient extends AbstractHttpCarrierClient
{
    protected function track(CarrierCredentials $credentials, TrackingRequest $request, Carrier $carrier, Shipment $shipment): CarrierTrackingResponse
    {
        if ($credentials->setting('requires_postal_code', true) && ! $request->postalCode) {
            throw new InvalidArgumentException('InPost tracking requires destination postal code. Configure destination_postal_code on the shipment metadata.');
        }

        $query = array_filter([
            $credentials->setting('postal_code_param', 'postalCode') => $request->postalCode,
            'country' => $credentials->setting('country', $request->destinationCountry),
        ]);

        $response = $this->http($credentials)
            ->withHeaders(array_filter([
                'Authorization' => $credentials->apiKey ? 'Bearer ' . $credentials->apiKey : null,
                'Accept-Language' => $credentials->setting('language', $request->language),
                'X-Country-Code' => $credentials->setting('country', $request->destinationCountry),
            ]))
            ->get($this->endpoint($credentials, 'v1/tracking/' . rawurlencode($request->trackingNumber)), $query);

        if ($response->status() === 404) {
            $payload = $response->json() ?? ['raw_body' => $response->body()];

            return new CarrierTrackingResponse(
                status: 'unknown',
                substatus: 'not_found',
                events: [[
                    'carrier_event_id' => $this->eventId('inpost', $request->trackingNumber, now()->toDateTimeString(), 'not_found'),
                    'raw_status' => 'not_found',
                    'description' => data_get($payload, 'message', 'Tracking information not found.'),
                    'event_at' => now(),
                    'raw_payload' => $payload,
                ]],
                raw: $payload,
            );
        }

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
