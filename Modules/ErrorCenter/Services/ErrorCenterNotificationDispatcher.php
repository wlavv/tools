<?php

namespace Modules\ErrorCenter\Services;

use Illuminate\Support\Facades\Log;
use Modules\ErrorCenter\Events\ErrorCenterNotificationRequested;
use Modules\ErrorCenter\Models\ErrorEvent;
use Modules\ErrorCenter\Models\ErrorOccurrence;
use Throwable;

class ErrorCenterNotificationDispatcher
{
    public function dispatchIfNeeded(ErrorEvent $errorEvent, ErrorOccurrence $occurrence, ?string $trigger): void
    {
        if ($trigger === null || ! $this->shouldNotify($errorEvent, $trigger)) {
            return;
        }

        $notification = $this->buildNotification($errorEvent, $occurrence, $trigger);

        try {
            event(new ErrorCenterNotificationRequested($notification));
            $this->dispatchToConfiguredService($notification);

            $errorEvent->forceFill([
                'last_notification_sent_at' => now(),
                'notification_count' => ((int) $errorEvent->notification_count) + 1,
                'last_notification_event' => $trigger,
            ])->save();
        } catch (Throwable $throwable) {
            Log::warning('Error Center notification dispatch failed.', [
                'error_event_id' => $errorEvent->id,
                'trigger' => $trigger,
                'exception' => $throwable->getMessage(),
            ]);
        }
    }

    public function shouldNotify(ErrorEvent $errorEvent, string $trigger): bool
    {
        if (! config('error-center.notifications.enabled', true)) {
            return false;
        }

        if (! (bool) config('error-center.notifications.triggers.' . $trigger, false)) {
            return false;
        }

        $environments = (array) config('error-center.notifications.environments', ['production']);

        if (! in_array($errorEvent->environment, $environments, true)) {
            return false;
        }

        if ($trigger === 'error_center.resolved_reopened') {
            return true;
        }

        if ($trigger === 'error_center.critical_created' && (int) $errorEvent->notification_count === 0) {
            return true;
        }

        return ! $this->isWithinCooldown($errorEvent);
    }

    public function buildNotification(ErrorEvent $errorEvent, ErrorOccurrence $occurrence, string $trigger): array
    {
        return [
            'type' => $trigger,
            'module' => 'error_center',
            'severity' => $errorEvent->severity,
            'title' => $this->buildTitle($errorEvent, $trigger),
            'body' => $this->buildBody($errorEvent, $trigger),
            'target_role' => config('error-center.notifications.target_role', 'technical_admin'),
            'target_permission' => config('error-center.notifications.target_permission', 'error_center.manage'),
            'data' => [
                'event' => $trigger,
                'error_event_id' => $errorEvent->id,
                'occurrence_id' => $occurrence->id,
                'title' => $errorEvent->title,
                'message' => $errorEvent->message,
                'error_type' => $errorEvent->error_type,
                'severity' => $errorEvent->severity,
                'status' => $errorEvent->status,
                'module' => $errorEvent->module,
                'source' => $errorEvent->source,
                'environment' => $errorEvent->environment,
                'occurrence_count' => $errorEvent->occurrence_count,
                'first_seen_at' => optional($errorEvent->first_seen_at)->toISOString(),
                'last_seen_at' => optional($errorEvent->last_seen_at)->toISOString(),
                'request_id' => $occurrence->request_id,
                'correlation_id' => $occurrence->correlation_id,
                'url' => url(trim((string) config('error-center.route_prefix', 'admin/error-center'), '/') . '/' . $errorEvent->id),
            ],
        ];
    }

    private function dispatchToConfiguredService(array $notification): void
    {
        $service = config('error-center.notifications.service');

        if (! $service) {
            return;
        }

        $instance = app($service);

        if (method_exists($instance, 'create')) {
            $instance->create($notification);
            return;
        }

        if (method_exists($instance, 'send')) {
            $instance->send($notification);
            return;
        }

        if (is_callable($instance)) {
            $instance($notification);
        }
    }

    private function buildTitle(ErrorEvent $errorEvent, string $trigger): string
    {
        return match ($trigger) {
            'error_center.resolved_reopened' => 'Erro resolvido voltou a ocorrer',
            'error_center.critical_created' => 'Erro crítico detectado',
            default => 'Novo erro detectado',
        };
    }

    private function buildBody(ErrorEvent $errorEvent, string $trigger): string
    {
        $module = $errorEvent->module ?: 'unknown';
        $environment = $errorEvent->environment ?: app()->environment();

        return match ($trigger) {
            'error_center.resolved_reopened' => "{$errorEvent->title} estava resolvido, mas voltou a ocorrer em {$environment}.",
            default => "{$errorEvent->title} no módulo {$module} em {$environment}.",
        };
    }

    private function isWithinCooldown(ErrorEvent $errorEvent): bool
    {
        if (! $errorEvent->last_notification_sent_at) {
            return false;
        }

        $cooldownMinutes = (int) config('error-center.notifications.cooldown_minutes', 30);

        return $errorEvent->last_notification_sent_at->greaterThan(now()->subMinutes($cooldownMinutes));
    }
}
