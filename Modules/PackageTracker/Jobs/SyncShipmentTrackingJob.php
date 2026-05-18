<?php

namespace Modules\PackageTracker\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\PackageTracker\Models\Shipment;
use Modules\PackageTracker\Services\TrackingService;
use Throwable;

class SyncShipmentTrackingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $shipmentId)
    {
        $this->onQueue(config('package_tracker.queue'));
    }

    public function handle(TrackingService $trackingService): void
    {
        $shipment = Shipment::query()->with('carrier')->findOrFail($this->shipmentId);

        if ($shipment->isTerminal()) {
            return;
        }

        try {
            $trackingService->syncShipment($shipment);
        } catch (Throwable $exception) {
            $trackingService->markFailedPoll($shipment, $exception);
            throw $exception;
        }
    }
}
