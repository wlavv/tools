<?php

namespace Modules\SystemLogs\Services;

use Modules\SystemLogs\Models\SystemLog;

class SystemLogsService{
    
    public function create(string $level, string $message, ?string $context = null){

        return SystemLog::create([
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'user_id' => auth()->id(),
        ]);
    }

    public function latest(int $limit = 100){
        
        return SystemLog::query()->latest()->limit($limit)->get();
    }
}
