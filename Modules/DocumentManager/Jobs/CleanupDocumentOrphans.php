<?php

namespace Modules\DocumentManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\DocumentManager\Services\AuditService;

class CleanupDocumentOrphans implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        $this->onQueue(config('documentmanager.queues.cleanup', 'dms_cleanup'));
    }

    public function handle(AuditService $audit): void
    {
        $audit->activity(null, 'cleanup.orphans.scan');
    }
}
