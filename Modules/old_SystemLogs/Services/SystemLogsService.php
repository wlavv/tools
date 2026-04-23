<?php

namespace Modules\SystemLogs\Services;

use Modules\SystemLogs\Models\SystemLog;

class SystemLogsService
{
    public function create($level, $message, $context = null)
    {
        return SystemLog::create([
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'user_id' => auth()->id()
        ]);
    }

    public function latest($limit = 100)
    {
        return SystemLog::latest()->limit($limit)->get();
    }
}
