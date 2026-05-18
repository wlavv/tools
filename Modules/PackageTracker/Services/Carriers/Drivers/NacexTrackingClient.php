<?php

namespace Modules\PackageTracker\Services\Carriers\Drivers;

use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\Shipment;
use Modules\PackageTracker\Services\Carriers\CarrierTrackingResponse;
use Modules\PackageTracker\Services\Carriers\Contracts\CarrierCredentials;
use Modules\PackageTracker\Services\Carriers\Contracts\TrackingRequest;
use Modules\PackageTracker\Services\Carriers\Support\AbstractHttpCarrierClient;
use Modules\PackageTracker\Services\Carriers\Support\CarrierPayloadReader as Reader;

class NacexTrackingClient extends AbstractHttpCarrierClient
{
    protected function track(CarrierCredentials $credentials, TrackingRequest $request, Carrier $carrier, Shipment $shipment): CarrierTrackingResponse
    {
        $path = $credentials->setting('tracking_path', 'ws');
        $params = array_filter([
            'method' => $credentials->setting('method_name', 'getEstadoEnvio'),
            'user' => $credentials->setting('user', $credentials->apiKey),
            'password' => $credentials->setting('password', $credentials->apiSecret),
            'tracking' => $request->trackingNumber,
            'ref' => $request->orderReference,
        ]);

        $response = $this->http($credentials)->get($this->endpoint($credentials, $path), $params);
        $response->throw();

        $payload = $this->decodePayload($response->body(), $response->json());
        $root = data_get($payload, 'data', $payload);
        $status = Reader::first($root, ['status', 'estado', 'codigo_estado', 'current_status'], 'unknown');
        $events = [];

        foreach (Reader::list($root, ['events', 'tracking', 'history', 'estados']) as $event) {
            $date = Reader::first($event, ['date', 'fecha', 'datetime', 'event_at']);
            $rawStatus = Reader::first($event, ['status', 'estado', 'description', 'descripcion'], $status);
            $location = Reader::first($event, ['location', 'delegacion', 'plaza', 'city']);
            $events[] = [
                'carrier_event_id' => $this->eventId('nacex', $request->trackingNumber, $date, $rawStatus, $location),
                'raw_status' => (string) $rawStatus,
                'description' => Reader::first($event, ['description', 'descripcion', 'estado']),
                'location' => $location,
                'event_at' => $date,
                'raw_payload' => is_array($event) ? $event : ['value' => $event],
            ];
        }

        return new CarrierTrackingResponse(
            status: (string) $status,
            substatus: Reader::first($root, ['substatus', 'descripcion']),
            lastLocation: Reader::first($root, ['events.0.location', 'delegacion', 'plaza']),
            estimatedDeliveryAt: Reader::first($root, ['estimated_delivery', 'fecha_prevista']),
            deliveredAt: str_contains(strtolower((string) $status), 'entreg') ? Reader::first($root, ['delivered_at', 'fecha_entrega']) : null,
            events: $events,
            raw: $payload,
        );
    }

    private function decodePayload(string $body, mixed $json): array
    {
        if (is_array($json)) {
            return $json;
        }

        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|\|/', $body))));
        return ['status' => $lines[0] ?? 'unknown', 'raw_lines' => $lines, 'raw_body' => $body];
    }
}
