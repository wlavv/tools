<?php

namespace Modules\WebCatalogue\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Modules\WebCatalogue\Jobs\RebuildStoreRecognitionFingerprintsJob;
use Modules\WebCatalogue\Models\Store;

class RebuildRecognitionFingerprintsCommand extends Command
{
    protected $signature = 'webcatalogue:recognition-rebuild-fingerprints
        {--store= : Rebuild only one store id}
        {--days= : Number of slices in the full cycle}
        {--bucket= : Force the slice index to run, zero-based}
        {--sync : Run immediately inside this command instead of dispatching a queue chain}
        {--dry-run : Show selected stores without dispatching jobs}';

    protected $description = 'Rebuild WebCatalogue visual recognition fingerprints by store slices.';

    public function handle(): int
    {
        $storeIds = $this->selectedStoreIds();

        if ($storeIds->isEmpty()) {
            $this->info('No stores selected for fingerprint rebuild.');
            return self::SUCCESS;
        }

        $this->info('Selected stores: ' . $storeIds->implode(', '));

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        $jobs = $storeIds
            ->map(fn (int $storeId) => new RebuildStoreRecognitionFingerprintsJob($storeId, 'scheduled'))
            ->values()
            ->all();

        if ($this->option('sync')) {
            foreach ($jobs as $job) {
                dispatch_sync($job);
            }

            $this->info('Fingerprints rebuilt synchronously for ' . count($jobs) . ' stores.');
            return self::SUCCESS;
        }

        Bus::chain($jobs)
            ->onQueue((string) config('webcatalogue.recognition.fingerprint_rebuild.queue', 'webcatalogue_recognition'))
            ->dispatch();

        $this->info('Queued sequential fingerprint rebuild for ' . count($jobs) . ' stores.');

        return self::SUCCESS;
    }

    protected function selectedStoreIds()
    {
        $specificStore = (int) $this->option('store');

        if ($specificStore > 0) {
            return collect([$specificStore]);
        }

        $days = max(1, (int) ($this->option('days') ?: config('webcatalogue.recognition.fingerprint_rebuild.days_per_cycle', 7)));
        $bucket = $this->option('bucket');
        $bucket = $bucket === null
            ? (now()->dayOfYear - 1) % $days
            : max(0, min($days - 1, (int) $bucket));

        return Store::query()
            ->orderBy('id')
            ->pluck('id')
            ->values()
            ->filter(fn ($storeId, int $index) => $index % $days === $bucket)
            ->values();
    }
}
