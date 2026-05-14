<?php

namespace Modules\StreamDeckAccess\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;
use Modules\StreamDeckAccess\Contracts\StreamDeckTask;
use Modules\StreamDeckAccess\Models\StreamDeckAccessLog;
use Modules\StreamDeckAccess\Models\StreamDeckAccessPoint;
use Throwable;

class RunStreamDeckTaskJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    public function __construct(
        public int $accessPointId,
        public int $logId,
    ) {
    }

    public function handle(): void
    {
        $accessPoint = StreamDeckAccessPoint::query()->findOrFail($this->accessPointId);
        $log = StreamDeckAccessLog::query()->findOrFail($this->logId);

        $taskClass = config('streamdeck-access.tasks.' . $accessPoint->task_key);

        if (! is_string($taskClass) || ! class_exists($taskClass)) {
            throw new InvalidArgumentException('Task class not found for key: ' . $accessPoint->task_key);
        }

        $task = app($taskClass);

        if (! $task instanceof StreamDeckTask) {
            throw new InvalidArgumentException($taskClass . ' must implement ' . StreamDeckTask::class);
        }

        try {
            $result = $task->handle($accessPoint, $log);

            $log->forceFill([
                'status' => 'completed',
                'response' => array_merge($log->response ?? [], [
                    'task_result' => $result,
                    'completed_at' => now()->toISOString(),
                ]),
            ])->save();
        } catch (Throwable $exception) {
            $log->forceFill([
                'status' => 'failed',
                'error' => $exception->getMessage(),
                'response' => array_merge($log->response ?? [], [
                    'failed_at' => now()->toISOString(),
                ]),
            ])->save();

            throw $exception;
        }
    }
}
