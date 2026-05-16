<?php

namespace Modules\WebCatalogue\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\WebCatalogue\Models\FingerprintRebuildLog;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Services\Recognition\InternalImageMatchService;
use Throwable;

class RebuildStoreRecognitionFingerprintsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200;

    public function __construct(public int $storeId, public string $trigger = 'scheduled')
    {
        $this->onQueue((string) config('webcatalogue.recognition.fingerprint_rebuild.queue', 'webcatalogue_recognition'));
    }

    public function handle(InternalImageMatchService $matcher): void
    {
        $store = Store::query()->find($this->storeId);

        if (!$store) {
            return;
        }

        $startedAt = now();
        $log = FingerprintRebuildLog::create([
            'id_store' => $store->id,
            'trigger' => $this->trigger,
            'status' => 'running',
            'started_at' => $startedAt,
        ]);

        try {
            $result = $matcher->rebuildStoreDataset($store);

            $log->update([
                'status' => 'completed',
                'processed' => (int) ($result['processed'] ?? 0),
                'created_count' => (int) ($result['created'] ?? 0),
                'updated_count' => (int) ($result['updated'] ?? 0),
                'failed_count' => (int) ($result['failed'] ?? 0),
                'algorithm' => $result['algorithm'] ?? null,
                'finished_at' => now(),
                'duration_ms' => max(1, $startedAt->diffInMilliseconds(now())),
                'metadata' => [
                    'queue' => $this->queue,
                ],
            ]);
        } catch (Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'finished_at' => now(),
                'duration_ms' => max(1, $startedAt->diffInMilliseconds(now())),
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
