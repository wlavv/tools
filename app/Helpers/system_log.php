<?php

if (!function_exists('system_log')) {
    function system_log($level, $message, $context = [])
    {
        try {
            \DB::table('wt_system_logs')->insert([
                'level' => $level,
                'message' => $message,
                'context' => json_encode($context),
                'user_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // nunca deixar falhar a app por causa de logs
        }
    }
}