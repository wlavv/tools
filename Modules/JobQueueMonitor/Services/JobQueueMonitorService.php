<?php

namespace Modules\JobQueueMonitor\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\JobQueueMonitor\Entities\JobQueueHealthCheck;
use Modules\JobQueueMonitor\Entities\JobQueueRun;
use Throwable;

class JobQueueMonitorService
{
    public function markStarted($event): void
    {
        $payload = $this->payload($event);
        $uuid = $payload['uuid'] ?? null;

        JobQueueRun::updateOrCreate(
            ['uuid' => $uuid],
            [
                'connection' => $event->connectionName ?? null,
                'queue' => $event->job ? $event->job->getQueue() : null,
                'job_name' => $payload['displayName'] ?? $payload['job'] ?? 'Unknown job',
                'status' => 'processing',
                'attempts' => $event->job ? $event->job->attempts() : 0,
                'payload' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'started_at' => now(),
            ]
        );
    }

    public function markProcessed($event): void
    {
        $payload = $this->payload($event);
        $uuid = $payload['uuid'] ?? null;
        $run = JobQueueRun::where('uuid', $uuid)->latest()->first();

        if (! $run) {
            $run = new JobQueueRun([
                'uuid' => $uuid,
                'connection' => $event->connectionName ?? null,
                'queue' => $event->job ? $event->job->getQueue() : null,
                'job_name' => $payload['displayName'] ?? $payload['job'] ?? 'Unknown job',
                'payload' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ]);
        }

        $startedAt = $run->started_at ? Carbon::parse($run->started_at) : now();
        $run->fill([
            'status' => 'success',
            'attempts' => $event->job ? $event->job->attempts() : $run->attempts,
            'finished_at' => now(),
            'duration_ms' => max(0, $startedAt->diffInMilliseconds(now())),
        ])->save();
    }

    public function markFailed($event): void
    {
        $payload = $this->payload($event);
        $uuid = $payload['uuid'] ?? null;
        $exception = $event->exception ?? null;

        $run = JobQueueRun::where('uuid', $uuid)->latest()->first();
        if (! $run) {
            $run = new JobQueueRun([
                'uuid' => $uuid,
                'connection' => $event->connectionName ?? null,
                'queue' => $event->job ? $event->job->getQueue() : null,
                'job_name' => $payload['displayName'] ?? $payload['job'] ?? 'Unknown job',
                'payload' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'started_at' => now(),
            ]);
        }

        $startedAt = $run->started_at ? Carbon::parse($run->started_at) : now();
        $run->fill([
            'status' => 'failed',
            'attempts' => $event->job ? $event->job->attempts() : $run->attempts,
            'failed_at' => now(),
            'finished_at' => now(),
            'duration_ms' => max(0, $startedAt->diffInMilliseconds(now())),
            'exception_message' => $exception ? $exception->getMessage() : null,
            'exception_file' => $exception ? $exception->getFile() : null,
            'exception_line' => $exception ? $exception->getLine() : null,
            'exception_trace' => $exception ? Str::limit($exception->getTraceAsString(), 12000) : null,
        ])->save();

        $this->createNotification($run);
        $this->sendFailureEmail($run);
    }

    public function dashboard(): array
    {
        $since = now()->subDay();

        return [
            'total_24h' => JobQueueRun::where('created_at', '>=', $since)->count(),
            'success_24h' => JobQueueRun::where('status', 'success')->where('created_at', '>=', $since)->count(),
            'failed_open' => JobQueueRun::openFailures()->count(),
            'processing' => JobQueueRun::where('status', 'processing')->count(),
            'retrying' => JobQueueRun::where('status', 'retrying')->count(),
            'avg_duration_ms' => (int) JobQueueRun::whereNotNull('duration_ms')->where('created_at', '>=', $since)->avg('duration_ms'),
            'latest_runs' => JobQueueRun::latest()->limit(30)->get(),
            'by_queue' => JobQueueRun::select('queue', 'status', DB::raw('count(*) as total'))->where('created_at', '>=', $since)->groupBy('queue', 'status')->get(),
            'health' => $this->healthCheck(),
        ];
    }

    public function healthCheck(): array
    {
        $checks = [];
        $failedOpen = JobQueueRun::openFailures()->count();
        $processingStale = JobQueueRun::where('status', 'processing')
            ->where('started_at', '<', now()->subMinutes((int) config('job-queue-monitor.stale_processing_minutes', 30)))
            ->count();
        $criticalWindow = JobQueueRun::where('status', 'failed')
            ->where('failed_at', '>=', now()->subMinutes((int) config('job-queue-monitor.critical_failures_window_minutes', 30)))
            ->count();

        $checks[] = $this->storeHealth('open_failures', 'Falhas abertas', $failedOpen === 0 ? 'ok' : 'warning', $failedOpen === 0 ? 'info' : 'danger', $failedOpen . ' falhas abertas');
        $checks[] = $this->storeHealth('stale_processing', 'Jobs presos em processing', $processingStale === 0 ? 'ok' : 'warning', $processingStale === 0 ? 'info' : 'warning', $processingStale . ' jobs em execução há demasiado tempo');
        $checks[] = $this->storeHealth('critical_failure_rate', 'Taxa de falhas recente', $criticalWindow >= (int) config('job-queue-monitor.critical_failures_threshold', 5) ? 'critical' : 'ok', $criticalWindow > 0 ? 'danger' : 'info', $criticalWindow . ' falhas na janela monitorizada');

        return $checks;
    }

    public function resolve(JobQueueRun $run, ?int $userId = null, ?string $note = null): void
    {
        $run->update([
            'resolved_at' => now(),
            'resolved_by' => $userId,
            'resolution_note' => $note,
        ]);
    }

    protected function storeHealth(string $key, string $label, string $status, string $severity, string $message): JobQueueHealthCheck
    {
        return JobQueueHealthCheck::updateOrCreate(
            ['check_key' => $key],
            ['label' => $label, 'status' => $status, 'severity' => $severity, 'message' => $message, 'checked_at' => now()]
        );
    }

    protected function payload($event): array
    {
        if (! isset($event->job) || ! method_exists($event->job, 'payload')) {
            return [];
        }

        try {
            return $event->job->payload() ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    protected function createNotification(JobQueueRun $run): void
    {
        if (! config('job-queue-monitor.notifications_enabled', true)) {
            return;
        }

        $table = config('job-queue-monitor.notifications_table', 'notifications');
        if (! DB::getSchemaBuilder()->hasTable($table)) {
            return;
        }

        try {
            DB::table($table)->insert([
                'type' => 'job_queue_failed',
                'title' => 'Job falhado: ' . $run->job_name,
                'message' => Str::limit((string) $run->exception_message, 500),
                'severity' => 'danger',
                'data' => json_encode(['run_id' => $run->id, 'queue' => $run->queue, 'job' => $run->job_name], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable $e) {
            // Nunca deixar uma falha de notification quebrar o worker.
        }
    }

    protected function sendFailureEmail(JobQueueRun $run): void
    {
        if (! config('job-queue-monitor.email_enabled', true) || ! config('job-queue-monitor.email_to')) {
            return;
        }

        try {
            $to = config('job-queue-monitor.email_to');
            $subject = config('job-queue-monitor.email_subject_prefix', '[Queue Alert]') . ' Job falhado #' . $run->id;
            $body = view('job-queue-monitor::emails.failed', ['run' => $run])->render();

            Mail::send([], [], function ($message) use ($to, $subject, $body) {
                $message->to($to)->subject($subject)->setBody($body, 'text/html');
            });
        } catch (Throwable $e) {
            // Evita loop de erro se o envio de email falhar dentro da queue.
        }
    }
}
