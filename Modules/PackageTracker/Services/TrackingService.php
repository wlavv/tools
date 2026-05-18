<?php

namespace Modules\PackageTracker\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\PackageTracker\Enums\TrackingStatus;
use Modules\PackageTracker\Models\Shipment;
use Modules\PackageTracker\Models\TrackingEvent;
use Throwable;

class TrackingService
{
    public function __construct(
        private readonly CarrierClientFactory $factory,
        private readonly StatusNormalizer $normalizer,
        private readonly PackageTrackerLogService $logs,
    ) {}

    public function syncShipment(Shipment $shipment): Shipment
    {
        $this->logs->info('Tracking sync started.', $this->logs->shipmentContext($shipment));

        try {
            $synced = DB::transaction(function () use ($shipment) {
                $shipment->loadMissing('carrier');
                $response = $this->factory->make($shipment->carrier)->fetchTracking($shipment->carrier, $shipment);

                $status = $this->normalizer->normalize($response->status);
                $eventAt = Carbon::now();

                foreach ($response->events as $event) {
                    TrackingEvent::query()->firstOrCreate([
                        'shipment_id' => $shipment->id,
                        'carrier_event_id' => $event['carrier_event_id'] ?? null,
                    ], [
                        'carrier_id' => $shipment->carrier_id,
                        'raw_status' => $event['raw_status'] ?? $response->status,
                        'normalized_status' => $event['normalized_status'] ?? $status,
                        'substatus' => $event['substatus'] ?? $response->substatus,
                        'description' => $event['description'] ?? null,
                        'location' => $event['location'] ?? $response->lastLocation,
                        'event_at' => $event['event_at'] ?? $eventAt,
                        'raw_payload' => $event['raw_payload'] ?? $event,
                    ]);
                }

                $shipment->fill([
                    'status' => $status,
                    'substatus' => $response->substatus,
                    'last_location' => $response->lastLocation,
                    'estimated_delivery_at' => $response->estimatedDeliveryAt ? Carbon::parse($response->estimatedDeliveryAt) : $shipment->estimated_delivery_at,
                    'delivered_at' => $response->deliveredAt ? Carbon::parse($response->deliveredAt) : $shipment->delivered_at,
                    'last_event_at' => $eventAt,
                    'last_polled_at' => Carbon::now(),
                    'next_poll_at' => in_array($status, TrackingStatus::terminalValues(), true) ? null : Carbon::now()->addMinutes(config('package_tracker.polling.default_interval_minutes')),
                    'has_exception' => in_array($status, ['exception', 'delivery_failed'], true),
                    'poll_attempts' => 0,
                    'metadata' => array_merge($shipment->metadata ?? [], ['last_raw_response' => $response->raw]),
                ])->save();

                return $shipment->refresh();
            });

            $this->logs->info('Tracking sync completed.', $this->logs->shipmentContext($synced, [
                'new_status' => $synced->status,
                'last_location' => $synced->last_location,
                'events_count' => $synced->events()->count(),
                'next_poll_at' => optional($synced->next_poll_at)->toDateTimeString(),
            ]));

            return $synced;
        } catch (Throwable $exception) {
            $this->logs->error('Tracking sync failed.', array_merge(
                $this->logs->shipmentContext($shipment),
                $this->logs->exceptionContext($exception)
            ));

            throw $exception;
        }
    }

    public function markFailedPoll(Shipment $shipment, Throwable $exception): void
    {
        $shipment->increment('poll_attempts');
        $shipment->forceFill([
            'last_polled_at' => Carbon::now(),
            'next_poll_at' => Carbon::now()->addMinutes(config('package_tracker.polling.default_interval_minutes')),
            'metadata' => array_merge($shipment->metadata ?? [], [
                'last_poll_error' => $exception->getMessage(),
            ]),
        ])->save();

        $this->logs->warning('Tracking poll failure recorded.', array_merge(
            $this->logs->shipmentContext($shipment->refresh(), [
                'next_poll_at' => optional($shipment->next_poll_at)->toDateTimeString(),
            ]),
            $this->logs->exceptionContext($exception)
        ));
    }

    public function refreshOperationalFlags(): int
    {
        $staleAfter = Carbon::now()->subHours(config('package_tracker.polling.stale_after_hours'));

        return Shipment::query()
            ->whereNotIn('status', TrackingStatus::terminalValues())
            ->where(function ($query) use ($staleAfter) {
                $query->whereNull('last_event_at')->orWhere('last_event_at', '<', $staleAfter);
            })
            ->update(['is_stale' => true]);
    }
}
