<?php

namespace Modules\PackageTracker\Services;

use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Services\Carriers\Discovery\CarrierIntegratorRegistry;

class CarrierDiscoveryService
{
    public function __construct(private readonly CarrierIntegratorRegistry $registry) {}

    public function availableIntegrators(): array
    {
        return $this->registry->all();
    }

    public function syncCarrierRecords(bool $force = false): array
    {
        $created = 0;
        $updated = 0;

        foreach ($this->registry->all() as $code => $integrator) {
            $data = [
                'name' => $integrator->name(),
                'driver' => $integrator->clientClass(),
                'api_base_url' => $integrator->defaultBaseUrl(),
                'supports_webhooks' => $integrator->supportsWebhooks(),
                'settings' => array_merge($integrator->defaultSettings(), [
                    '_integrator' => [
                        'code' => $integrator->code(),
                        'capabilities' => $integrator->capabilities(),
                        'credential_schema' => $integrator->credentialSchema(),
                        'tracking_number_hints' => $integrator->trackingNumberHints(),
                    ],
                ]),
            ];

            $carrier = Carrier::query()->firstOrNew(['code' => $code]);

            if (!$carrier->exists) {
                $carrier->fill(array_merge($data, ['is_active' => false]))->save();
                $created++;
                continue;
            }

            if ($force) {
                $carrier->fill($data)->save();
                $updated++;
            }
        }

        return compact('created', 'updated');
    }
}
