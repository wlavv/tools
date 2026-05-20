<?php

namespace Modules\SystemTools\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

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
            } elseif (($tool['type'] ?? 'artisan') === 'shell') {
                $output = $this->runShell($tool['command'] ?? []);
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
            'composer_dump_autoload' => $this->composerDumpAutoload(),
            default => throw new RuntimeException('Invalid custom handler.'),
        };
    }

    protected function composerDumpAutoload(): string
    {
        $command = $this->composerCommand();

        if (!$command) {
            throw new RuntimeException(
                'Composer binary not found. Configure SYSTEM_TOOLS_COMPOSER_BINARY or place composer.phar in the project root.'
            );
        }

        return $this->runShell(array_merge($command, ['dump-autoload', '--optimize']));
    }

    protected function composerCommand(): ?array
    {
        $configured = trim((string) env('SYSTEM_TOOLS_COMPOSER_BINARY', ''));

        if ($configured !== '') {
            return [$configured];
        }

        $composerPhar = base_path('composer.phar');
        if (is_file($composerPhar)) {
            return [PHP_BINARY, $composerPhar];
        }

        $binary = PHP_OS_FAMILY === 'Windows' ? $this->which('composer.bat') ?: $this->which('composer') : $this->which('composer');

        return $binary ? [$binary] : null;
    }

    protected function which(string $binary): ?string
    {
        $command = PHP_OS_FAMILY === 'Windows'
            ? ['where', $binary]
            : ['sh', '-lc', 'command -v ' . escapeshellarg($binary)];

        try {
            $output = $this->runShell($command, false);
        } catch (\Throwable) {
            return null;
        }

        $firstLine = trim(strtok($output, PHP_EOL) ?: '');

        return $firstLine !== '' ? $firstLine : null;
    }

    protected function runShell(array|string $command, bool $useBasePath = true): string
    {
        if (is_string($command)) {
            $command = $this->splitShellCommand($command);
        }

        $command = array_values(array_filter($command, fn ($part) => is_string($part) && $part !== ''));

        if (!$command) {
            throw new RuntimeException('Shell command is empty.');
        }

        $descriptorSpec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open(
            array_map('strval', $command),
            $descriptorSpec,
            $pipes,
            $useBasePath ? base_path() : null
        );

        if (!is_resource($process)) {
            throw new RuntimeException('Could not start shell command.');
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);
        $output = $this->stripAnsi(trim(implode(PHP_EOL, array_filter([trim((string) $stdout), trim((string) $stderr)]))));

        if ($exitCode !== 0) {
            throw new RuntimeException(trim(sprintf(
                "Command failed with exit code %d.%s%s",
                $exitCode,
                $output !== '' ? PHP_EOL : '',
                $output
            )));
        }

        return $output !== '' ? $output : 'Command executed without output.';
    }

    protected function stripAnsi(string $output): string
    {
        return (string) preg_replace('/\e\[[0-9;?]*[A-Za-z]/', '', $output);
    }

    protected function splitShellCommand(string $command): array
    {
        $command = trim($command);

        if ($command === '') {
            return [];
        }

        return str_getcsv($command, ' ', '"', '\\');
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
