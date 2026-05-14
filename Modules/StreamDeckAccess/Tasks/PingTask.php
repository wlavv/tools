<?php

namespace Modules\StreamDeckAccess\Tasks;

use Modules\StreamDeckAccess\Contracts\StreamDeckTask;
use Modules\StreamDeckAccess\Models\StreamDeckAccessLog;
use Modules\StreamDeckAccess\Models\StreamDeckAccessPoint;

class PingTask implements StreamDeckTask
{
    public function handle(StreamDeckAccessPoint $accessPoint, StreamDeckAccessLog $log): array
    {
        return [
            'message' => 'pong',
            'handled_at' => now()->toISOString(),
        ];
    }
}
