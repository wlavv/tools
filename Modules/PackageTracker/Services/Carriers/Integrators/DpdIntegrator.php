<?php

namespace Modules\PackageTracker\Services\Carriers\Integrators;

use Modules\PackageTracker\Services\Carriers\Contracts\AbstractCarrierIntegrator;
use Modules\PackageTracker\Services\Carriers\Drivers\DpdTrackingClient;

class DpdIntegrator extends AbstractCarrierIntegrator
{
    public function code(): string
    {
        return 'dpd';
    }

    public function name(): string
    {
        return 'DPD';
    }

    public function clientClass(): string
    {
        return DpdTrackingClient::class;
    }

    public function defaultBaseUrl(): ?string
    {
        return env('PACKAGE_TRACKER_DPD_BASE_URL', $this->baseUrlForCountry());
    }

    public function supportsWebhooks(): bool
    {
        return false;
    }

    public function defaultSettings(): array
    {
        return [
            'api_type' => env('PACKAGE_TRACKER_DPD_API_TYPE', 'status_tracking'),
            'country' => env('PACKAGE_TRACKER_DPD_COUNTRY'),
            'tracking_path' => env('PACKAGE_TRACKER_DPD_TRACKING_PATH', 'status/tracking'),
            'tracking_param' => env('PACKAGE_TRACKER_DPD_TRACKING_PARAM', 'pknr'),
            'method' => env('PACKAGE_TRACKER_DPD_METHOD', 'GET'),
            'detail' => env('PACKAGE_TRACKER_DPD_DETAIL', '3'),
            'show_all' => env('PACKAGE_TRACKER_DPD_SHOW_ALL', '1'),
            'lang' => env('PACKAGE_TRACKER_DPD_LANG', 'en'),
        ];
    }

    public function credentialSchema(): array
    {
        return [['key' => 'api_key', 'label' => 'Api Key', 'type' => 'password', 'required' => true]];
    }

    public function trackingNumberHints(): array
    {
        return ['For the documented Baltic DPD API, use the 14 numeric parcel number.'];
    }

    private function baseUrlForCountry(): ?string
    {
        return match (strtoupper((string) env('PACKAGE_TRACKER_DPD_COUNTRY'))) {
            'LT' => 'https://esiunta.dpd.lt/api/v1',
            'LV' => 'https://eserviss.dpd.lv/api/v1',
            'EE' => 'https://telli.dpd.ee/api/v1',
            default => null,
        };
    }
}
