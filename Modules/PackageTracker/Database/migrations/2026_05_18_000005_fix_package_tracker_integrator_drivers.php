<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\PackageTracker\Services\Carriers\Contracts\CarrierIntegratorInterface;
use Modules\PackageTracker\Services\Carriers\Integrators\CttIntegrator;
use Modules\PackageTracker\Services\Carriers\Integrators\DhlIntegrator;
use Modules\PackageTracker\Services\Carriers\Integrators\DpdIntegrator;
use Modules\PackageTracker\Services\Carriers\Integrators\InpostIntegrator;
use Modules\PackageTracker\Services\Carriers\Integrators\MondialRelayIntegrator;
use Modules\PackageTracker\Services\Carriers\Integrators\NacexIntegrator;
use Modules\PackageTracker\Services\Carriers\Integrators\UpsIntegrator;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('package_tracker_carriers')) {
            return;
        }

        foreach ($this->integrators() as $integratorClass) {
            if (! class_exists($integratorClass)) {
                continue;
            }

            $integrator = app($integratorClass);

            if (! $integrator instanceof CarrierIntegratorInterface) {
                continue;
            }

            DB::table('package_tracker_carriers')
                ->where('code', $integrator->code())
                ->where(function ($query) use ($integratorClass) {
                    $query->where('driver', $integratorClass)
                        ->orWhereNull('driver');
                })
                ->update([
                    'driver' => $integrator->clientClass(),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        //
    }

    private function integrators(): array
    {
        return [
            UpsIntegrator::class,
            DpdIntegrator::class,
            NacexIntegrator::class,
            InpostIntegrator::class,
            MondialRelayIntegrator::class,
            DhlIntegrator::class,
            CttIntegrator::class,
        ];
    }
};
