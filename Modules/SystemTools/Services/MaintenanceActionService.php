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
            'npm_build' => $this->npmBuild(),
            'npm_install' => $this->npmInstall(),
            'npm_diagnostics' => $this->npmDiagnostics(),
            'asset_loading_diagnostics' => $this->assetLoadingDiagnostics(),
            'git_restore_system_tools_config' => $this->gitRestoreSystemToolsConfig(),
            default => throw new RuntimeException('Invalid custom handler.'),
        };
    }

    protected function gitRestoreSystemToolsConfig(): string
    {
        $command = $this->gitCommand();

        if (!$command) {
            throw new RuntimeException(
                'Git binary not found. Configure SYSTEM_TOOLS_GIT_BINARY or make git available on the server.'
            );
        }

        return $this->runShell(array_merge($command, [
            'checkout',
            '--',
            'Modules/SystemTools/Config/config.php',
        ]));
    }

    protected function gitCommand(): ?array
    {
        $configured = trim((string) (config('system-tools.git_binary') ?: env('SYSTEM_TOOLS_GIT_BINARY', '')));

        if ($configured !== '') {
            return [$configured];
        }

        $cpanelGit = '/usr/local/cpanel/3rdparty/bin/git';
        if (is_file($cpanelGit) && is_executable($cpanelGit)) {
            return [$cpanelGit];
        }

        $binary = PHP_OS_FAMILY === 'Windows' ? $this->which('git.exe') ?: $this->which('git') : $this->which('git');

        return $binary ? [$binary] : null;
    }

    protected function npmBuild(): string
    {
        $command = $this->npmCommand();

        if (!$command) {
            throw new RuntimeException(
                'NPM binary not found. Configure SYSTEM_TOOLS_NPM_BINARY or install Node.js/NPM on the server.'
            );
        }

        return $this->runShell(array_merge($command, ['run', 'build']), true, $this->npmEnvironment($command));
    }

    protected function npmInstall(): string
    {
        $command = $this->npmCommand();

        if (!$command) {
            throw new RuntimeException(
                'NPM binary not found. Configure SYSTEM_TOOLS_NPM_BINARY or install Node.js/NPM on the server.'
            );
        }

        $installCommand = is_file(base_path('package-lock.json'))
            ? ['ci', '--include=dev']
            : ['install'];

        return $this->runShell(array_merge($command, $installCommand), true, $this->npmEnvironment($command));
    }

    protected function npmDiagnostics(): string
    {
        $lines = [
            'Configured SYSTEM_TOOLS_NPM_BINARY: ' . ($this->configuredNpmBinary() ?: '[empty]'),
            'PATH: ' . (getenv('PATH') ?: '[empty]'),
            'Base path: ' . base_path(),
            'package.json: ' . (is_file(base_path('package.json')) ? '[OK]' : '[missing]'),
            'node_modules: ' . (is_dir(base_path('node_modules')) ? '[OK]' : '[missing]'),
            'public/build writable: ' . (is_dir(public_path('build')) && is_writable(public_path('build')) ? '[OK]' : '[missing or not writable]'),
            '',
            'NPM candidates:',
        ];

        $candidates = $this->npmCandidates();

        if (!$candidates) {
            $lines[] = '- [none found]';
        }

        foreach ($candidates as $candidate) {
            $label = implode(' ', $candidate);

            try {
                $version = trim($this->runShell(array_merge($candidate, ['--version']), false, $this->npmEnvironment($candidate)));
                $lines[] = '- [OK] ' . $label . ' => ' . $version;
            } catch (\Throwable $e) {
                $lines[] = '- [FAIL] ' . $label . ' => ' . $e->getMessage();
            }
        }

        return implode(PHP_EOL, $lines);
    }

    protected function assetLoadingDiagnostics(): string
    {
        $lines = [
            'APP_URL: ' . (string) config('app.url'),
            'ASSET_URL: ' . (string) config('app.asset_url'),
            'public_path: ' . public_path(),
            'public/hot: ' . (is_file(public_path('hot')) ? '[PRESENT - remove it in production]' : '[missing OK]'),
            '',
            'Vite manifest:',
        ];

        $manifestPath = public_path('build/manifest.json');

        if (!is_file($manifestPath)) {
            $lines[] = '- [missing] public/build/manifest.json';
        } else {
            $lines[] = '- [OK] public/build/manifest.json (' . filesize($manifestPath) . ' bytes)';

            $manifest = json_decode((string) file_get_contents($manifestPath), true);
            if (!is_array($manifest)) {
                $lines[] = '- [FAIL] manifest is not valid JSON';
            } else {
                foreach (['resources/sass/app.scss', 'resources/js/app.js'] as $entry) {
                    $file = $manifest[$entry]['file'] ?? null;
                    $lines[] = $this->assetDiagnosticLine($entry, $file ? 'build/' . $file : null);
                }
            }
        }

        $lines[] = '';
        $lines[] = 'Legacy layout assets:';

        foreach ([
            'admin/css/app.css',
            'admin/css/sweetalert2.min.css',
            'admin/css/dropzone.min.css',
            'admin/js/sweetalert2.min.js',
            'admin/js/dropzone.min.js',
            'assets/css/lsg-select2.css',
            'assets/js/lsg-select2.js',
        ] as $asset) {
            $lines[] = $this->assetDiagnosticLine($asset, $asset);
        }

        return implode(PHP_EOL, $lines);
    }

    protected function assetDiagnosticLine(string $label, ?string $relativePath): string
    {
        if (!$relativePath) {
            return '- [missing manifest entry] ' . $label;
        }

        $fullPath = public_path(str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
        $url = asset($relativePath);

        if (!is_file($fullPath)) {
            return '- [missing] ' . $label . ' => ' . $relativePath . ' | ' . $url;
        }

        return '- [OK] ' . $label . ' => ' . $relativePath . ' (' . filesize($fullPath) . ' bytes) | ' . $url;
    }

    protected function npmCommand(): ?array
    {
        $candidates = $this->npmCandidates();

        return $candidates[0] ?? null;
    }

    protected function configuredNpmBinary(): string
    {
        return trim((string) (config('system-tools.npm_binary') ?: env('SYSTEM_TOOLS_NPM_BINARY', '')));
    }

    protected function npmCandidates(): array
    {
        $configured = $this->configuredNpmBinary();
        $candidates = [];

        if ($configured !== '') {
            $candidates[] = PHP_OS_FAMILY === 'Windows'
                ? ['cmd.exe', '/C', $configured]
                : [$configured];
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $binary = $this->which('npm.cmd') ?: $this->which('npm');

            if ($binary) {
                $candidates[] = ['cmd.exe', '/C', $binary];
            }

            return $this->uniqueCommands($candidates);
        }

        $commonPaths = array_merge(
            [
                '/usr/local/bin/npm',
                '/usr/bin/npm',
                '/bin/npm',
                '/opt/cpanel/ea-nodejs*/bin/npm',
            ],
            $this->descendingAltNodeNpmPaths()
        );

        foreach ($commonPaths as $path) {
            foreach (glob($path) ?: [] as $candidate) {
                if (is_file($candidate) && is_executable($candidate)) {
                    $candidates[] = [$candidate];
                }
            }
        }

        $binary = $this->which('npm');

        if ($binary) {
            $candidates[] = [$binary];
        }

        return $this->uniqueCommands($candidates);
    }

    protected function descendingAltNodeNpmPaths(): array
    {
        $paths = glob('/opt/alt/alt-nodejs*/root/usr/bin/npm') ?: [];

        usort($paths, function (string $a, string $b): int {
            return $this->nodeMajorFromPath($b) <=> $this->nodeMajorFromPath($a);
        });

        return $paths;
    }

    protected function nodeMajorFromPath(string $path): int
    {
        return preg_match('/alt-nodejs(\d+)/', $path, $matches) ? (int) $matches[1] : 0;
    }

    protected function npmEnvironment(array $command): array
    {
        $env = [
            'NPM_CONFIG_PRODUCTION' => 'false',
        ];

        $npmPath = $command[count($command) - 1] ?? null;

        if (is_string($npmPath) && $npmPath !== '') {
            $dir = dirname($npmPath);
            $path = getenv('PATH') ?: '';
            $env['PATH'] = $dir . ($path !== '' ? PATH_SEPARATOR . $path : '');
        }

        return $env;
    }

    protected function uniqueCommands(array $commands): array
    {
        $unique = [];

        foreach ($commands as $command) {
            $key = implode("\0", $command);
            $unique[$key] = $command;
        }

        return array_values($unique);
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

    protected function runShell(array|string $command, bool $useBasePath = true, array $env = []): string
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
            $useBasePath ? base_path() : null,
            $env ?: null
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
