<?php

namespace Modules\PackageTracker\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\PackageTracker\Models\Carrier;
use Modules\PackageTracker\Services\Carriers\Integrators\DpdIntegrator;
use Modules\PackageTracker\Services\Carriers\Integrators\InpostIntegrator;
use Modules\PackageTracker\Services\Carriers\Integrators\MondialRelayIntegrator;
use Modules\PackageTracker\Services\Carriers\Integrators\NacexIntegrator;
use Modules\PackageTracker\Services\Carriers\Integrators\TrackingMoreDpdIntegrator;
use Modules\PackageTracker\Services\Carriers\Integrators\UpsIntegrator;
use Modules\PackageTracker\Services\Carriers\ManualCarrierClient;
use Modules\PackageTracker\Services\Carriers\MockCarrierClient;

class PackageTrackerCarrierSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCoreCarriers();
        $this->seedIntegratorCarriers();
    }

    private function seedCoreCarriers(): void
    {
        foreach ($this->coreCarriers() as $carrier) {
            Carrier::query()->updateOrCreate(
                ['code' => $carrier['code']],
                $carrier
            );
        }
    }

    private function seedIntegratorCarriers(): void
    {
        foreach ($this->integrators() as $integrator) {
            Carrier::query()->updateOrCreate(
                ['code' => $integrator->code()],
                [
                    'name' => $integrator->name(),
                    'driver' => $integrator->clientClass(),
                    'api_base_url' => $integrator->defaultBaseUrl(),
                    'supports_webhooks' => $integrator->supportsWebhooks(),
                    'is_active' => true,
                    'settings' => array_merge($integrator->defaultSettings(), [
                        '_integrator' => [
                            'code' => $integrator->code(),
                            'capabilities' => $integrator->capabilities(),
                            'credential_schema' => $integrator->credentialSchema(),
                            'tracking_number_hints' => $integrator->trackingNumberHints(),
                        ],
                    ]),
                ]
            );
        }
    }

    private function coreCarriers(): array
    {
        return [
            [
                'code' => 'manual',
                'name' => 'Manual / Generic',
                'driver' => ManualCarrierClient::class,
                'is_active' => true,
                'supports_webhooks' => false,
                'settings' => [],
            ],
            [
                'code' => 'mock',
                'name' => 'Mock Carrier',
                'driver' => MockCarrierClient::class,
                'is_active' => true,
                'supports_webhooks' => false,
                'settings' => [],
            ],
        ];
    }

    private function integrators(): array
    {
        return [
            new UpsIntegrator(),
            new DpdIntegrator(),
            new TrackingMoreDpdIntegrator(),
            new NacexIntegrator(),
            new InpostIntegrator(),
            new MondialRelayIntegrator(),
        ];
    }
}
