<?php

namespace Modules\IntegrationHealth\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\IntegrationHealth\Models\IntegrationHealthEvent;
use Modules\IntegrationHealth\Models\IntegrationHealthHeartbeat;
use Modules\IntegrationHealth\Models\IntegrationHealthMetric;
use Modules\IntegrationHealth\Models\IntegrationHealthService as HealthService;

class IntegrationHealthService
{
    public function bootstrapDefaultServices(): void
    {
        foreach (config('integration-health.default_services', []) as $service) {
            HealthService::firstOrCreate(
                ['slug' => $service['slug']],
                [
                    'name' => $service['name'],
                    'type' => $service['type'] ?? 'api',
                    'status' => 'unknown',
                    'health_score' => 100,
                    'is_enabled' => true,
                ]
            );
        }
    }

    public function dashboard(): array
    {
        $this->bootstrapDefaultServices();
        $this->evaluateHeartbeats();

        $services = HealthService::query()
            ->where('is_enabled', true)
            ->withCount([
                'events as open_events_count' => fn ($q) => $q->whereNull('resolved_at'),
                'events as critical_events_count' => fn ($q) => $q->whereNull('resolved_at')->whereIn('severity', ['critical', 'fatal']),
            ])
            ->orderByRaw("FIELD(status, 'offline', 'degraded', 'unknown', 'online')")
            ->orderBy('name')
            ->get();

        $openEvents = IntegrationHealthEvent::query()
            ->with('service')
            ->whereNull('resolved_at')
            ->latest()
            ->limit(10)
            ->get();

        $score = (int) round($services->avg('health_score') ?: 100);

        return [
            'services' => $services,
            'openEvents' => $openEvents,
            'summary' => [
                'system_score' => $score,
                'services_total' => $services->count(),
                'online' => $services->where('status', 'online')->count(),
                'degraded' => $services->where('status', 'degraded')->count(),
                'offline' => $services->where('status', 'offline')->count(),
                'unknown' => $services->where('status', 'unknown')->count(),
                'open_events' => IntegrationHealthEvent::whereNull('resolved_at')->count(),
                'critical_events' => IntegrationHealthEvent::whereNull('resolved_at')->whereIn('severity', ['critical', 'fatal'])->count(),
            ],
        ];
    }

    public function recordHeartbeat(string $serviceSlug, array $payload = [], ?int $responseTimeMs = null, string $status = 'online'): HealthService
    {
        return DB::transaction(function () use ($serviceSlug, $payload, $responseTimeMs, $status) {
            $service = HealthService::firstOrCreate(
                ['slug' => $serviceSlug],
                ['name' => str($serviceSlug)->replace('-', ' ')->title(), 'type' => 'custom']
            );

            IntegrationHealthHeartbeat::create([
                'service_id' => $service->id,
                'service_slug' => $service->slug,
                'heartbeat_at' => now(),
                'response_time_ms' => $responseTimeMs,
                'status' => $status,
                'payload' => $payload,
            ]);

            $service->update([
                'status' => $status,
                'health_score' => $this->calculateServiceScore($service, $status, $responseTimeMs),
                'last_seen_at' => now(),
                'last_success_at' => $status === 'online' ? now() : $service->last_success_at,
                'avg_response_time_ms' => $this->calculateAverageLatency($service->slug, $responseTimeMs),
            ]);

            return $service->fresh();
        });
    }

    public function recordEvent(string $serviceSlug, string $severity, string $eventType, string $title, ?string $message = null, array $payload = []): IntegrationHealthEvent
    {
        return DB::transaction(function () use ($serviceSlug, $severity, $eventType, $title, $message, $payload) {
            $service = HealthService::firstOrCreate(
                ['slug' => $serviceSlug],
                ['name' => str($serviceSlug)->replace('-', ' ')->title(), 'type' => 'custom']
            );

            $event = IntegrationHealthEvent::create([
                'service_id' => $service->id,
                'service_slug' => $service->slug,
                'severity' => $severity,
                'event_type' => $eventType,
                'title' => $title,
                'message' => $message,
                'payload' => $payload,
            ]);

            $service->update([
                'status' => in_array($severity, ['critical', 'fatal'], true) ? 'offline' : 'degraded',
                'health_score' => max(0, $service->health_score - $this->severityPenalty($severity)),
                'last_error_at' => now(),
                'last_error_message' => $message ?: $title,
            ]);

            return $event;
        });
    }

    public function recordMetric(string $serviceSlug, string $metric, float $value, ?string $unit = null, array $payload = []): IntegrationHealthMetric
    {
        $service = HealthService::firstOrCreate(
            ['slug' => $serviceSlug],
            ['name' => str($serviceSlug)->replace('-', ' ')->title(), 'type' => 'custom']
        );

        return IntegrationHealthMetric::create([
            'service_id' => $service->id,
            'service_slug' => $service->slug,
            'metric' => $metric,
            'value' => $value,
            'unit' => $unit,
            'payload' => $payload,
            'recorded_at' => now(),
        ]);
    }

    public function resolveEvent(IntegrationHealthEvent $event, ?int $userId = null): IntegrationHealthEvent
    {
        $event->update([
            'resolved_at' => now(),
            'resolved_by' => $userId,
        ]);

        if ($event->service) {
            $openCount = $event->service->events()->whereNull('resolved_at')->count();
            if ($openCount === 0) {
                $event->service->update([
                    'status' => $event->service->last_seen_at ? 'online' : 'unknown',
                    'health_score' => min(100, max(70, $event->service->health_score + 10)),
                ]);
            }
        }

        return $event->fresh();
    }

    public function evaluateHeartbeats(): void
    {
        $warningMinutes = (int) config('integration-health.thresholds.heartbeat_warning_minutes', 10);
        $criticalMinutes = (int) config('integration-health.thresholds.heartbeat_critical_minutes', 30);

        HealthService::query()->where('is_enabled', true)->chunkById(50, function ($services) use ($warningMinutes, $criticalMinutes) {
            foreach ($services as $service) {
                if (!$service->last_seen_at) {
                    continue;
                }

                $minutes = Carbon::parse($service->last_seen_at)->diffInMinutes(now());

                if ($minutes >= $criticalMinutes && $service->status !== 'offline') {
                    $service->update(['status' => 'offline', 'health_score' => min($service->health_score, 20)]);
                    $this->recordEvent($service->slug, 'critical', 'heartbeat_missing', 'Heartbeat crítico em falta', "Sem heartbeat há {$minutes} minutos.");
                } elseif ($minutes >= $warningMinutes && $service->status === 'online') {
                    $service->update(['status' => 'degraded', 'health_score' => min($service->health_score, 65)]);
                    $this->recordEvent($service->slug, 'warning', 'heartbeat_late', 'Heartbeat atrasado', "Sem heartbeat há {$minutes} minutos.");
                }
            }
        });
    }

    protected function calculateAverageLatency(string $serviceSlug, ?int $responseTimeMs): ?int
    {
        if ($responseTimeMs === null) {
            return null;
        }

        $avg = IntegrationHealthHeartbeat::where('service_slug', $serviceSlug)
            ->whereNotNull('response_time_ms')
            ->latest('heartbeat_at')
            ->limit(25)
            ->avg('response_time_ms');

        return $avg ? (int) round($avg) : $responseTimeMs;
    }

    protected function calculateServiceScore(HealthService $service, string $status, ?int $responseTimeMs): int
    {
        $score = match ($status) {
            'online' => 100,
            'degraded' => 65,
            'offline' => 15,
            default => 50,
        };

        $warningLatency = (int) config('integration-health.thresholds.latency_warning_ms', 1000);
        $criticalLatency = (int) config('integration-health.thresholds.latency_critical_ms', 3000);

        if ($responseTimeMs !== null && $responseTimeMs >= $criticalLatency) {
            $score -= 35;
        } elseif ($responseTimeMs !== null && $responseTimeMs >= $warningLatency) {
            $score -= 15;
        }

        return max(0, min(100, $score));
    }

    protected function severityPenalty(string $severity): int
    {
        return match ($severity) {
            'notice' => 3,
            'warning' => 10,
            'error' => 20,
            'critical' => 45,
            'fatal' => 60,
            default => 1,
        };
    }
}
