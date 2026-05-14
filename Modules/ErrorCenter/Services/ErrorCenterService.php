<?php

namespace Modules\ErrorCenter\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\ErrorCenter\Models\ErrorEvent;
use Modules\ErrorCenter\Models\ErrorOccurrence;
use Throwable;

class ErrorCenterService
{
    public function __construct(
        private readonly ErrorContextSanitizer $sanitizer,
        private readonly ErrorHashGenerator $hashGenerator,
        private readonly ErrorCenterNotificationDispatcher $notificationDispatcher,
    ) {
    }

    public function captureException(Throwable $throwable, array $context = []): ?ErrorEvent
    {
        if (! config('error-center.enabled', true) || ! config('error-center.capture.enabled', true)) {
            return null;
        }

        $sanitizedContext = $this->sanitizer->sanitize($context);
        $hash = $this->hashGenerator->generate($throwable, $sanitizedContext);
        $severity = $this->determineSeverity($throwable, $sanitizedContext);
        $now = now();

        /** @var array{0: ErrorEvent, 1: ErrorOccurrence, 2: bool, 3: bool} $result */
        $result = DB::transaction(function () use ($throwable, $sanitizedContext, $hash, $severity, $now): array {
            $event = ErrorEvent::query()->where('hash', $hash)->lockForUpdate()->first();
            $isNewEvent = false;
            $wasResolved = false;

            if (! $event) {
                try {
                    $event = ErrorEvent::query()->create([
                        'hash' => $hash,
                        'title' => $this->buildTitle($throwable),
                        'message' => $this->limitText($throwable->getMessage(), 'message_length'),
                        'error_type' => get_class($throwable),
                        'severity' => $severity,
                        'status' => ErrorEvent::STATUS_NEW,
                        'module' => Arr::get($sanitizedContext, 'module', 'unknown'),
                        'source' => Arr::get($sanitizedContext, 'source', 'backend'),
                        'environment' => Arr::get($sanitizedContext, 'environment', app()->environment()),
                        'first_seen_at' => $now,
                        'last_seen_at' => $now,
                        'occurrence_count' => 1,
                        'affected_users_count' => 0,
                    ]);

                    $isNewEvent = true;
                } catch (QueryException) {
                    $event = ErrorEvent::query()->where('hash', $hash)->lockForUpdate()->firstOrFail();
                }
            }

            if (! $isNewEvent) {
                $wasResolved = $event->status === ErrorEvent::STATUS_RESOLVED;

                $updates = [
                    'last_seen_at' => $now,
                    'occurrence_count' => DB::raw('occurrence_count + 1'),
                    'updated_at' => $now,
                ];

                if ($wasResolved) {
                    $updates['status'] = ErrorEvent::STATUS_NEW;
                    $updates['resolved_at'] = null;
                    $updates['resolved_by'] = null;
                }

                ErrorEvent::query()->whereKey($event->id)->update($updates);
                $event = ErrorEvent::query()->whereKey($event->id)->firstOrFail();
            }

            $occurrence = ErrorOccurrence::query()->create([
                'error_event_id' => $event->id,
                'occurred_at' => $now,
                'user_id' => $this->nullableString(Arr::get($sanitizedContext, 'user_id')),
                'tenant_id' => $this->nullableString(Arr::get($sanitizedContext, 'tenant_id')),
                'request_id' => $this->nullableString(Arr::get($sanitizedContext, 'request_id')),
                'correlation_id' => $this->nullableString(Arr::get($sanitizedContext, 'correlation_id')),
                'endpoint' => $this->nullableString(Arr::get($sanitizedContext, 'endpoint')),
                'http_method' => $this->nullableString(Arr::get($sanitizedContext, 'http_method')),
                'status_code' => Arr::get($sanitizedContext, 'status_code'),
                'ip_address' => $this->nullableString(Arr::get($sanitizedContext, 'ip_address')),
                'user_agent' => $this->nullableString(Arr::get($sanitizedContext, 'user_agent')),
                'stack_trace' => $this->limitText($throwable->getTraceAsString(), 'stack_trace_length'),
                'payload_snapshot' => Arr::get($sanitizedContext, 'payload'),
                'context_json' => [
                    'headers' => Arr::get($sanitizedContext, 'headers'),
                    'query' => Arr::get($sanitizedContext, 'query'),
                    'params' => Arr::get($sanitizedContext, 'params'),
                    'extra' => Arr::get($sanitizedContext, 'extra'),
                ],
            ]);

            $this->trackAffectedUser($event, Arr::get($sanitizedContext, 'user_id'));

            return [$event->fresh() ?: $event, $occurrence, $isNewEvent, $wasResolved];
        }, 3);

        [$event, $occurrence, $isNewEvent, $wasResolved] = $result;

        $trigger = $this->determineNotificationTrigger($event, $isNewEvent, $wasResolved);
        $this->notificationDispatcher->dispatchIfNeeded($event, $occurrence, $trigger);

        return $event->fresh() ?: $event;
    }

    public function determineSeverity(Throwable $throwable, array $context = []): string
    {
        $statusCode = (int) Arr::get($context, 'status_code', 500);
        $module = Str::lower((string) Arr::get($context, 'module', ''));

        foreach ((array) config('error-center.severity.critical_exception_classes', []) as $class) {
            if ($throwable instanceof $class) {
                return ErrorEvent::SEVERITY_CRITICAL;
            }
        }

        if (in_array($statusCode, (array) config('error-center.severity.critical_status_codes', [503]), true)) {
            return ErrorEvent::SEVERITY_CRITICAL;
        }

        if ($statusCode >= 500 && in_array($module, (array) config('error-center.severity.critical_modules', []), true)) {
            return ErrorEvent::SEVERITY_CRITICAL;
        }

        if ($statusCode >= 500) {
            return ErrorEvent::SEVERITY_ERROR;
        }

        if ($statusCode >= 400) {
            return ErrorEvent::SEVERITY_WARNING;
        }

        return ErrorEvent::SEVERITY_ERROR;
    }

    public function determineNotificationTrigger(ErrorEvent $event, bool $isNewEvent, bool $wasResolved): ?string
    {
        if ($wasResolved) {
            return 'error_center.resolved_reopened';
        }

        if ($isNewEvent && $event->severity === ErrorEvent::SEVERITY_CRITICAL) {
            return 'error_center.critical_created';
        }

        if ($isNewEvent && $event->severity === ErrorEvent::SEVERITY_ERROR) {
            return 'error_center.error_created';
        }

        return null;
    }

    private function buildTitle(Throwable $throwable): string
    {
        $title = class_basename($throwable) . ': ' . ($throwable->getMessage() ?: 'Unhandled exception');

        return $this->limitText($title, 'title_length');
    }

    private function limitText(?string $value, string $configKey): ?string
    {
        if ($value === null) {
            return null;
        }

        $max = (int) config('error-center.limits.' . $configKey, 65535);

        if (Str::length($value) <= $max) {
            return $value;
        }

        return Str::limit($value, $max, '...[TRUNCATED]');
    }

    private function trackAffectedUser(ErrorEvent $event, mixed $userId): void
    {
        if ($userId === null || $userId === '') {
            return;
        }

        $inserted = DB::table('error_event_users')->insertOrIgnore([
            'error_event_id' => $event->id,
            'user_id' => (string) $userId,
            'first_seen_at' => now(),
        ]);

        if ($inserted > 0) {
            ErrorEvent::query()->whereKey($event->id)->increment('affected_users_count');
        }
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
