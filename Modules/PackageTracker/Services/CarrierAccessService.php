<?php

namespace Modules\PackageTracker\Services;

use Carbon\Carbon;
use Modules\PackageTracker\Models\CarrierSuggestion;
use Modules\PackageTracker\Models\ClientCarrierAccess;

class CarrierAccessService
{
    public function enabledCarrierCodes(?string $clientKey): array
    {
        if (!$clientKey) {
            return [];
        }

        return ClientCarrierAccess::query()
            ->where('client_key', $clientKey)
            ->where('is_enabled', true)
            ->pluck('carrier_code')
            ->all();
    }

    public function canUse(?string $clientKey, string $carrierCode): bool
    {
        if (config('package_tracker.access.allow_without_client_key', true) && !$clientKey) {
            return true;
        }

        return ClientCarrierAccess::query()
            ->where('client_key', $clientKey)
            ->where('carrier_code', $carrierCode)
            ->where('is_enabled', true)
            ->exists();
    }

    public function enable(string $clientKey, string $carrierCode, array $credentials = [], array $settings = []): ClientCarrierAccess
    {
        return ClientCarrierAccess::query()->updateOrCreate([
            'client_key' => $clientKey,
            'carrier_code' => $carrierCode,
        ], [
            'is_enabled' => true,
            'credentials' => $credentials,
            'settings' => $settings,
            'enabled_at' => Carbon::now(),
            'disabled_at' => null,
        ]);
    }

    public function suggest(?string $clientKey, string $trackingNumber, ?string $requestedCarrierCode, ?string $suggestedCarrierCode, string $reason, float $confidence = 75.0, array $rawPayload = []): CarrierSuggestion
    {
        return CarrierSuggestion::query()->create([
            'client_key' => $clientKey,
            'tracking_number' => $trackingNumber,
            'requested_carrier_code' => $requestedCarrierCode,
            'suggested_carrier_code' => $suggestedCarrierCode,
            'status' => 'open',
            'confidence' => $confidence,
            'reason' => $reason,
            'raw_payload' => $rawPayload,
        ]);
    }
}
