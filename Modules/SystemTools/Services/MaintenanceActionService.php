<?php

namespace Modules\SystemTools\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaintenanceActionService
{
    public function all(): array
    {
        $tools = config('system-tools.tools', []);

        if (isset($tools['actions']) && is_array($tools['actions'])) {
            $tools = $tools['actions'];
        }

        return is_array($tools) ? $tools : [];
    }

    public function run(string $action, bool $external = false): array
    {
        $tools = $this->all();

        if (!array_key_exists($action, $tools)) {
            return [
                'success' => false,
                'message' => 'Action not found.',
                'debug' => [
                    'requested_action' => $action,
                    'available_actions' => array_keys($tools),
                    'config_key' => 'system-tools.tools',
                ],
            ];
        }

        $tool = $tools[$action];

        if ($external && empty($tool['external'])) {
            return [
                'success' => false,
                'message' => 'This action is not allowed externally.',
            ];
        }

        try {
            $startedAt = microtime(true);

            if (($tool['type'] ?? 'artisan') === 'custom') {
                $output = $this->runCustom((string) ($tool['handler'] ?? ''));
            } else {
                Artisan::call($tool['command'], $tool['params'] ?? []);
                $output = trim(Artisan::output());
            }

            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            $this->log('info', 'SystemTools action executed', [
                'action' => $action,
                'label' => $tool['label'] ?? $action,
                'external' => $external,
                'duration_ms' => $durationMs,
            ]);

            return [
                'success' => true,
                'message' => ($tool['label'] ?? $action) . ' executed successfully.',
                'output' => $output !== '' ? $output : 'Command executed without output.',
                'duration_ms' => $durationMs,
                'action' => $action,
                'label' => $tool['label'] ?? $action,
            ];
        } catch (\Throwable $e) {
            $this->log('error', 'SystemTools action failed', [
                'action' => $action,
                'external' => $external,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'action' => $action,
                'label' => $tool['label'] ?? $action,
            ];
        }
    }

    protected function runCustom(string $handler): string
    {
        return match ($handler) {
            'system_info' => $this->systemInfo(),
            'writable_paths' => $this->writablePaths(),
            default => throw new \RuntimeException('Invalid custom handler.'),
        };
    }

    protected function systemInfo(): string
    {
        $lines = [
            'PHP: ' . PHP_VERSION,
            'Laravel: ' . app()->version(),
            'Environment: ' . app()->environment(),
            'Debug: ' . (config('app.debug') ? 'true' : 'false'),
            'URL: ' . config('app.url'),
            'Timezone: ' . config('app.timezone'),
        ];

        try {
            DB::connection()->getPdo();
            $lines[] = 'Database [default]: OK';
        } catch (\Throwable $e) {
            $lines[] = 'Database [default]: FAIL - ' . $e->getMessage();
        }

        if (config('database.connections.mysql2')) {
            try {
                DB::connection('mysql2')->getPdo();
                $lines[] = 'Database [mysql2]: OK';
            } catch (\Throwable $e) {
                $lines[] = 'Database [mysql2]: FAIL - ' . $e->getMessage();
            }
        }

        if (function_exists('disk_free_space')) {
            $free = @disk_free_space(base_path());
            if ($free !== false) {
                $lines[] = 'Disk free: ' . number_format($free / 1024 / 1024, 2) . ' MB';
            }
        }

        return implode(PHP_EOL, $lines);
    }

    protected function writablePaths(): string
    {
        $paths = [
            storage_path(),
            storage_path('logs'),
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            base_path('bootstrap/cache'),
        ];

        $lines = [];

        foreach ($paths as $path) {
            $lines[] = (is_writable($path) ? '[OK] ' : '[FAIL] ') . $path;
        }

        return implode(PHP_EOL, $lines);
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        if (function_exists('system_log')) {
            system_log($level, $message, $context);
            return;
        }

        Log::log($level, $message, $context);
    }
}
