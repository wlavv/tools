<?php

namespace Modules\PackageTracker\Services\Carriers\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\Shipment;
use Modules\PackageTracker\Services\Carriers\CarrierClientInterface;
use Modules\PackageTracker\Services\Carriers\CarrierTrackingResponse;
use Modules\PackageTracker\Services\Carriers\Contracts\CarrierCredentials;
use Modules\PackageTracker\Services\Carriers\Contracts\TrackingRequest;

abstract class AbstractHttpCarrierClient implements CarrierClientInterface
{
    public function fetchTracking(Carrier $carrier, Shipment $shipment): CarrierTrackingResponse
    {
        return $this->track(CarrierCredentials::fromCarrier($carrier), TrackingRequest::fromShipment($carrier, $shipment), $carrier, $shipment);
    }

    abstract protected function track(CarrierCredentials $credentials, TrackingRequest $request, Carrier $carrier, Shipment $shipment): CarrierTrackingResponse;

    public function healthCheck(Carrier $carrier): bool
    {
        return (bool) CarrierCredentials::fromCarrier($carrier)->baseUrl;
    }

    protected function http(CarrierCredentials $credentials): PendingRequest
    {
        return Http::timeout((int) config('package_tracker.http.timeout', 20))
            ->retry(
                (int) config('package_tracker.http.retries', 1),
                (int) config('package_tracker.http.retry_sleep_ms', 250)
            )
            ->acceptJson();
    }

    protected function endpoint(CarrierCredentials $credentials, ?string $path = null): string
    {
        $base = rtrim((string) $credentials->baseUrl, '/');
        $path = ltrim((string) $path, '/');

        return $path === '' ? $base : $base . '/' . $path;
    }

    protected function eventId(string $carrierCode, string $trackingNumber, ?string $date, ?string $status, ?string $location = null): string
    {
        return sha1(implode('|', [$carrierCode, $trackingNumber, $date, $status, $location]));
    }
}
