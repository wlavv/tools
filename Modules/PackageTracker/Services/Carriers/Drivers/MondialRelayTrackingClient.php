<?php

namespace Modules\PackageTracker\Services\Carriers\Drivers;

use Illuminate\Support\Str;
use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\Shipment;
use Modules\PackageTracker\Services\Carriers\CarrierTrackingResponse;
use Modules\PackageTracker\Services\Carriers\Contracts\CarrierCredentials;
use Modules\PackageTracker\Services\Carriers\Contracts\TrackingRequest;
use Modules\PackageTracker\Services\Carriers\Support\AbstractHttpCarrierClient;
use Modules\PackageTracker\Services\Carriers\Support\CarrierPayloadReader as Reader;

class MondialRelayTrackingClient extends AbstractHttpCarrierClient
{
    protected function track(CarrierCredentials $credentials, TrackingRequest $request, Carrier $carrier, Shipment $shipment): CarrierTrackingResponse
    {
        $enseigne = (string) $credentials->setting('enseigne', $credentials->apiKey);
        $privateKey = (string) $credentials->setting('private_key', $credentials->apiSecret);
        $language = strtoupper((string) $credentials->setting('language', $request->language ?: 'FR'));

        $params = [
            'Enseigne' => $enseigne,
            'Expedition' => $request->trackingNumber,
            'Langue' => substr($language, 0, 2),
        ];
        $params['Security'] = $this->securityHash($params, $privateKey);

        $response = $this->http($credentials)
            ->asForm()
            ->post($this->endpoint($credentials, 'Web_Services.asmx/WSI2_TracingColisDetaille'), $params);

        $response->throw();
        $payload = $this->decodeXmlishResponse($response->body());
        $status = Reader::first($payload, ['STAT', 'status', 'Libelle01'], 'unknown');
        $events = [];

        foreach (Reader::list($payload, ['Tracing.Libelle', 'events']) as $idx => $event) {
            $event = is_array($event) ? $event : ['description' => $event];
            $date = Reader::first($event, ['Date', 'date', 'event_at']);
            $rawStatus = Reader::first($event, ['STAT', 'status', 'description'], $status);
            $location = Reader::first($event, ['Relais', 'location', 'Ville']);
            $events[] = [
                'carrier_event_id' => $this->eventId('mondial_relay', $request->trackingNumber, $date, $rawStatus, $location ?: (string) $idx),
                'raw_status' => (string) $rawStatus,
                'description' => Reader::first($event, ['Libelle', 'description', 'status']),
                'location' => $location,
                'event_at' => $date,
                'raw_payload' => $event,
            ];
        }

        return new CarrierTrackingResponse(
            status: (string) $status,
            substatus: Reader::first($payload, ['Libelle01', 'message']),
            lastLocation: Reader::first($payload, ['Tracing.Libelle.0.Relais', 'location']),
            estimatedDeliveryAt: Reader::first($payload, ['DateLivraisonPrevue', 'estimated_delivery']),
            deliveredAt: Str::contains(Str::lower((string) Reader::first($payload, ['Libelle01', 'status'])), ['livré', 'delivered']) ? Reader::first($payload, ['DateLivraison', 'delivered_at']) : null,
            events: $events,
            raw: $payload,
        );
    }

    private function securityHash(array $params, string $privateKey): string
    {
        return strtoupper(md5(implode('', array_values($params)) . $privateKey));
    }

    private function decodeXmlishResponse(string $body): array
    {
        $xml = @simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml !== false) {
            $json = json_encode($xml);
            $array = json_decode((string) $json, true);
            return is_array($array) ? $array : ['raw_body' => $body];
        }

        return ['raw_body' => $body, 'status' => 'unknown'];
    }
}
