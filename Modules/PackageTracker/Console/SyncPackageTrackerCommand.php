<?php

namespace Modules\PackageTracker\Console;

use Illuminate\Console\Command;
use Modules\PackageTracker\Jobs\SyncShipmentTrackingJob;
use Modules\PackageTracker\Models\Shipment;
use Modules\PackageTracker\Services\TrackingService;
use Throwable;

class SyncPackageTrackerCommand extends Command
{
    protected $signature = 'package-tracker:sync {--limit=100 : Maximum number of shipments to queue} {--sync : Run synchronously}';

    protected $description = 'Queue or run package tracking synchronization for due shipments.';

    public function handle(TrackingService $trackingService): int
    {
        $limit = (int) $this->option('limit');

        $shipments = Shipment::query()
            ->whereHas('carrier', fn ($q) => $q->where('is_active', true))
            ->whereNotIn('status', ['delivered', 'returned', 'cancelled'])
            ->where(function ($query) {
                $query->whereNull('next_poll_at')->orWhere('next_poll_at', '<=', now());
            })
            ->orderBy('next_poll_at')
            ->limit($limit)
            ->get();

        foreach ($shipments as $shipment) {
            if ($this->option('sync')) {
                try {
                    $trackingService->syncShipment($shipment);
                } catch (Throwable $exception) {
                    $trackingService->markFailedPoll($shipment, $exception);
                    $this->warn("Shipment {$shipment->id} sync failed: {$exception->getMessage()}");
                }
            } else {
                SyncShipmentTrackingJob::dispatch($shipment->id);
            }
        }

        $trackingService->refreshOperationalFlags();

        $this->info('Package tracking sync prepared: ' . $shipments->count() . ' shipments.');

        return self::SUCCESS;
    }
}
