<?php

namespace Modules\PackageTracker\Services;

use Illuminate\Support\Str;
use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Models\Shipment;
use Modules\PackageTracker\Services\Carriers\CarrierTrackingResponse;
use RuntimeException;
use Throwable;

class CarrierLookupService
{
    public function __construct(
        private readonly CarrierAccessService $access,
        private readonly CarrierClientFactory $factory,
    ) {}

    public function lookup(string $trackingNumber, ?string $carrierCode = null, ?string $clientKey = null, array $metadata = []): array
    {
        $carrier = $carrierCode ? Carrier::query()->where('code', $carrierCode)->where('is_active', true)->first() : null;

        if ($carrierCode && !$carrier) {
            $this->access->suggest($clientKey, $trackingNumber, $carrierCode, null, 'Requested carrier does not exist or is not active.', 0);
            throw new RuntimeException("Carrier {$carrierCode} is not available.");
        }

        if ($carrier && !$this->access->canUse($clientKey, $carrier->code)) {
            $this->access->suggest($clientKey, $trackingNumber, $carrier->code, $carrier->code, 'Client tried to use a carrier that is available globally but not enabled in their plan.', 100);
            throw new RuntimeException("Carrier {$carrier->code} is not enabled for this client.");
        }

        if ($carrier) {
            $response = $this->probe($carrier, $trackingNumber, $clientKey, $metadata);
            if ($this->looksFound($response)) {
                return ['carrier' => $carrier, 'response' => $response, 'suggestion' => null];
            }
        }

        if (!config('package_tracker.discovery.probe_uncontracted_carriers', false)) {
            $this->access->suggest($clientKey, $trackingNumber, $carrierCode, null, 'Tracking was not found and cross-carrier probing is disabled.', 25);
            return ['carrier' => $carrier, 'response' => $response ?? null, 'suggestion' => null];
        }

        $contracted = $this->access->enabledCarrierCodes($clientKey);
        $excluded = array_filter(array_merge($contracted, [$carrierCode]));

        foreach (Carrier::query()->where('is_active', true)->whereNotIn('code', $excluded)->get() as $candidate) {
            try {
                $candidateResponse = $this->probe($candidate, $trackingNumber, $clientKey, $metadata);
                if (!$this->looksFound($candidateResponse)) {
                    continue;
                }

                $suggestion = $this->access->suggest(
                    $clientKey,
                    $trackingNumber,
                    $carrierCode,
                    $candidate->code,
                    "Tracking not found in contracted carrier. It appears to belong to {$candidate->name}.",
                    85,
                    $candidateResponse->raw
                );

                return ['carrier' => $candidate, 'response' => $candidateResponse, 'suggestion' => $suggestion];
            } catch (Throwable) {
                continue;
            }
        }

        $this->access->suggest($clientKey, $trackingNumber, $carrierCode, null, 'Tracking was not found in contracted carrier and no alternative carrier matched.', 15);

        return ['carrier' => $carrier, 'response' => $response ?? null, 'suggestion' => null];
    }

    private function probe(Carrier $carrier, string $trackingNumber, ?string $clientKey, array $metadata): CarrierTrackingResponse
    {
        $shipment = new Shipment([
            'carrier_id' => $carrier->id,
            'tracking_number' => $trackingNumber,
            'external_reference' => $metadata['external_reference'] ?? 'probe-' . Str::uuid()->toString(),
            'store_code' => $metadata['store_code'] ?? null,
            'order_reference' => $metadata['order_reference'] ?? null,
            'metadata' => array_merge($metadata, ['client_key' => $clientKey, 'probe_only' => true]),
        ]);

        $shipment->setRelation('carrier', $carrier);

        return $this->factory->make($carrier)->fetchTracking($carrier, $shipment);
    }

    private function looksFound(?CarrierTrackingResponse $response): bool
    {
        if (!$response) {
            return false;
        }

        if (in_array(strtolower($response->status), ['not_found', 'unknown', 'error', ''], true)) {
            return false;
        }

        return count($response->events) > 0 || filled($response->lastLocation) || filled($response->deliveredAt) || filled($response->estimatedDeliveryAt);
    }
}
