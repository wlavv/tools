<?php

namespace Modules\PackageTracker\Console;

use Illuminate\Console\Command;
use Modules\PackageTracker\Services\CarrierDiscoveryService;

class InstallPackageTrackerCommand extends Command
{
    protected $signature = 'package-tracker:install-carriers {--force : Update existing carriers with discovered integrator values}';

    protected $description = 'Discover carrier integrator files and create or refresh carrier records.';

    public function handle(CarrierDiscoveryService $discovery): int
    {
        $result = $discovery->syncCarrierRecords((bool) $this->option('force'));

        $this->info("Carrier integrators discovered and prepared. Created: {$result['created']}; Updated: {$result['updated']}.");
        $this->line('Available integrators: ' . implode(', ', array_keys($discovery->availableIntegrators())));
        $this->warn('Enable carriers globally and per client before production polling. Credentials can live on the carrier or client-carrier access record.');

        return self::SUCCESS;
    }
}
