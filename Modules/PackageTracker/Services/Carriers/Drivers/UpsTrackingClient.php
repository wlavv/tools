<?php

namespace Modules\PackageTracker\Services\Carriers\Drivers;

use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\Shipment;
use Modules\PackageTracker\Services\Carriers\CarrierTrackingResponse;
use Modules\PackageTracker\Services\Carriers\Contracts\CarrierCredentials;
use Modules\PackageTracker\Services\Carriers\Contracts\TrackingRequest;
use Modules\PackageTracker\Services\Carriers\Support\AbstractHttpCarrierClient;
use Modules\PackageTracker\Services\Carriers\Support\CarrierPayloadReader as Reader;

class UpsTrackingClient extends AbstractHttpCarrierClient
{
    protected function track(CarrierCredentials $credentials, TrackingRequest $request, Carrier $carrier, Shipment $shipment): CarrierTrackingResponse
    {
        $token = $this->resolveAccessToken($credentials);

        $response = $this->http($credentials)
            ->withToken($token)
            ->withHeaders(array_filter([
                'transId' => $credentials->setting('transaction_id', uniqid('lsg_', true)),
                'transactionSrc' => $credentials->setting('transaction_src', 'LSGPackageTracker'),
            ]))
            ->get($this->endpoint($credentials, 'api/track/v1/details/' . rawurlencode($request->trackingNumber)), array_filter([
                'locale' => $credentials->setting('locale', 'en_US'),
                'returnSignature' => $credentials->setting('return_signature'),
            ]));

        $response->throw();
        $payload = $response->json() ?? [];
        $package = data_get($payload, 'trackResponse.shipment.0.package.0', []);
        $current = data_get($package, 'currentStatus', []);
        $status = Reader::first($current, ['code', 'description'], 'unknown');
        $events = [];

        foreach (Reader::list($package, ['activity']) as $activity) {
            $date = trim((string) data_get($activity, 'date') . ' ' . (string) data_get($activity, 'time'));
            $rawStatus = Reader::first($activity, ['status.code', 'status.description'], $status);
            $location = Reader::first($activity, ['location.address.city', 'location.address.countryCode']);

            $events[] = [
                'carrier_event_id' => $this->eventId('ups', $request->trackingNumber, $date, $rawStatus, $location),
                'raw_status' => (string) $rawStatus,
                'description' => Reader::first($activity, ['status.description']),
                'location' => $location,
                'event_at' => $date !== '' ? $date : null,
                'raw_payload' => $activity,
            ];
        }

        return new CarrierTrackingResponse(
            status: (string) $status,
            substatus: Reader::first($current, ['description']),
            lastLocation: Reader::first($package, ['activity.0.location.address.city', 'activity.0.location.address.countryCode']),
            estimatedDeliveryAt: Reader::first($package, ['deliveryDate.0.date', 'deliveryTime.endTime']),
            deliveredAt: strtolower((string) Reader::first($current, ['description'])) === 'delivered' ? Reader::first($package, ['deliveryDate.0.date']) : null,
            events: $events,
            raw: $payload,
        );
    }

    private function resolveAccessToken(CarrierCredentials $credentials): string
    {
        if ($credentials->apiKey && $credentials->apiSecret) {
            $cacheKey = 'package_tracker:ups:access_token:' . sha1($credentials->apiKey . '|' . $credentials->baseUrl);

            return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($credentials) {
                $response = $this->http($credentials)
                    ->asForm()
                    ->withBasicAuth($credentials->apiKey, $credentials->apiSecret)
                    ->post($this->endpoint($credentials, 'security/v1/oauth/token'), ['grant_type' => 'client_credentials']);

                $response->throw();

                $token = (string) data_get($response->json(), 'access_token');

                if ($token === '') {
                    throw new InvalidArgumentException('UPS OAuth response did not include an access token.');
                }

                return $token;
            });
        }

        if ($token = $credentials->setting('access_token')) {
            return $token;
        }

        throw new InvalidArgumentException('UPS credentials missing. Configure client_id/client_secret or a valid access_token.');
    }
}
