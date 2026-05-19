<?php

namespace Modules\AIConsensus\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\AIConsensus\Models\AIConsensusRun;
use ZipArchive;

class AIConsensusModulePackageService
{
    public function buildZip(AIConsensusRun $run): string
    {
        $run->loadMissing(['template', 'outputs', 'providerResponses.provider']);

        $blueprint = $this->resolveBlueprint($run);
        $moduleName = $this->resolveModuleName($run, $blueprint);
        $moduleSlug = Str::kebab($moduleName);
        $basePath = storage_path("app/ai-consensus/module-packages/run-{$run->id}");
        $modulePath = $basePath . DIRECTORY_SEPARATOR . $moduleName;

        if (File::exists($modulePath)) {
            File::deleteDirectory($modulePath);
        }

        File::ensureDirectoryExists($modulePath);
        File::ensureDirectoryExists($basePath);

        $files = $this->moduleFiles($run, $blueprint, $moduleName, $moduleSlug);

        foreach ($files as $relativePath => $content) {
            $target = $modulePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
            File::ensureDirectoryExists(dirname($target));
            File::put($target, $content);
        }

        return $this->createArchive($modulePath, $basePath, $moduleName);
    }

    private function createArchive(string $modulePath, string $basePath, string $moduleName): string
    {
        if (class_exists(ZipArchive::class)) {
            return $this->createZipArchive($modulePath, $basePath, $moduleName);
        }

        return $this->createTarGzArchive($modulePath, $basePath, $moduleName);
    }

    private function createZipArchive(string $modulePath, string $basePath, string $moduleName): string
    {
        $zipPath = $basePath . DIRECTORY_SEPARATOR . "{$moduleName}.zip";

        if (File::exists($zipPath)) {
            File::delete($zipPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create module package.');
        }

        foreach (File::allFiles($modulePath) as $file) {
            $zip->addFile($file->getPathname(), $moduleName . '/' . $file->getRelativePathname());
        }

        $zip->close();

        return $zipPath;
    }

    private function createTarGzArchive(string $modulePath, string $basePath, string $moduleName): string
    {
        $tarPath = $basePath . DIRECTORY_SEPARATOR . "{$moduleName}.tar";
        $archivePath = $tarPath . '.gz';

        foreach ([$tarPath, $archivePath] as $path) {
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        $archive = new \PharData($tarPath);
        foreach (File::allFiles($modulePath) as $file) {
            $archive->addFile($file->getPathname(), $moduleName . '/' . str_replace('\\', '/', $file->getRelativePathname()));
        }

        $archive->compress(\Phar::GZ);
        unset($archive);

        if (File::exists($tarPath)) {
            File::delete($tarPath);
        }

        return $archivePath;
    }

    private function resolveBlueprint(AIConsensusRun $run): array
    {
        $output = $run->outputs->last();
        if (is_array($output?->json_payload) && $output->json_payload !== []) {
            return $output->json_payload;
        }

        foreach ([$output?->content, $run->final_output] as $content) {
            $decoded = $this->decodeJsonContent((string) $content);
            if ($decoded !== []) {
                return $decoded;
            }
        }

        return [
            'title' => $run->title ?: 'Generated LSG Module',
            'summary' => $run->final_output ?: 'No structured blueprint was available. Review the original AI Consensus run before implementation.',
        ];
    }

    private function decodeJsonContent(string $content): array
    {
        $content = trim($content);
        if ($content === '') {
            return [];
        }

        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/is', $content, $matches)) {
            $decoded = json_decode($matches[1], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $decoded = json_decode(substr($content, $start, $end - $start + 1), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function resolveModuleName(AIConsensusRun $run, array $blueprint): string
    {
        $name = data_get($blueprint, 'module_name')
            ?: data_get($blueprint, 'module.name')
            ?: data_get($blueprint, 'name')
            ?: data_get($blueprint, 'title')
            ?: data_get($run->input_payload, 'title')
            ?: $run->title
            ?: 'GeneratedModule';

        $studly = Str::studly(preg_replace('/[^A-Za-z0-9]+/', ' ', (string) $name));
        $studly = preg_replace('/^[0-9]+/', '', $studly) ?: 'GeneratedModule';

        return $studly;
    }

    private function moduleFiles(AIConsensusRun $run, array $blueprint, string $moduleName, string $moduleSlug): array
    {
        $snake = Str::snake($moduleName);
        $namespace = "Modules\\{$moduleName}";
        $permissionPrefix = 'permission_' . $snake;
        $blueprintJson = $this->json($blueprint);
        $lsgRules = $this->json(config('ai-consensus-lsg', []));
        $runCost = $run->providerResponses->sum(fn ($response) => (float) ($response->cost_estimate ?? 0));

        return [
            'module.json' => $this->json([
                'name' => $moduleName,
                'alias' => $moduleSlug,
                'description' => data_get($blueprint, 'description', data_get($blueprint, 'summary', 'Generated from AI Consensus blueprint.')),
                'keywords' => ['lsg', 'module', 'ai-consensus'],
                'priority' => 0,
                'providers' => [
                    "{$namespace}\\Providers\\{$moduleName}ServiceProvider",
                ],
                'permissions' => [
                    "{$permissionPrefix}_view",
                    "{$permissionPrefix}_create",
                    "{$permissionPrefix}_edit",
                    "{$permissionPrefix}_delete",
                ],
            ]),
            'README.md' => $this->readme($run, $moduleName, $runCost),
            'AI_CONSENSUS_RUN.md' => $this->runReport($run, $runCost),
            'blueprint.json' => $blueprintJson,
            'lsg-standard.json' => $lsgRules,
            "Providers/{$moduleName}ServiceProvider.php" => $this->serviceProvider($moduleName, $namespace, $moduleSlug),
            'Routes/web.php' => $this->webRoutes($moduleName, $namespace, $moduleSlug),
            'Routes/api.php' => $this->apiRoutes(),
            'Config/config.php' => "<?php\n\nreturn [\n    'name' => '{$moduleName}',\n];\n",
            "Http/Controllers/{$moduleName}Controller.php" => $this->controller($moduleName, $namespace, $moduleSlug),
            "Services/{$moduleName}Service.php" => $this->service($moduleName, $namespace),
            'Resources/views/index.blade.php' => $this->indexView($moduleName),
            'Resources/views/Includes/css.blade.php' => '',
            'Resources/views/Includes/js.blade.php' => '',
            'Resources/lang/en/messages.php' => "<?php\n\nreturn [\n    'title' => '{$moduleName}',\n];\n",
            'Resources/lang/pt/messages.php' => "<?php\n\nreturn [\n    'title' => '{$moduleName}',\n];\n",
            'Database/Migrations/.gitkeep' => '',
            'Models/.gitkeep' => '',
        ];
    }

    private function readme(AIConsensusRun $run, string $moduleName, float $runCost): string
    {
        return <<<MD
# {$moduleName}

Generated as a review package from AI Consensus run #{$run->id}.

This package is intentionally not installed automatically. Review the blueprint, generated skeleton, permissions, migrations and security notes before copying anything into `/Modules`.

## Source

- Source module: {$run->source_module}
- Source type: {$run->source_type}
- Source id: {$run->source_id}
- Output type: {$run->output_type}
- Final score: {$run->final_score}
- Estimated run cost: \${$this->money($runCost)}

## Safety

- Do not execute generated code without human review.
- Do not write outside the target module directory.
- Do not alter `.env` from generated instructions.
- Treat migrations and permissions as drafts until validated.

MD;
    }

    private function runReport(AIConsensusRun $run, float $runCost): string
    {
        $providers = $run->providerResponses
            ->map(fn ($response) => sprintf(
                '- %s: %s, score %s, cost $%s',
                $response->provider?->name ?: 'Internal provider',
                $response->status,
                $response->score ?? '-',
                $this->money((float) ($response->cost_estimate ?? 0))
            ))
            ->implode("\n");

        return <<<MD
# AI Consensus Run #{$run->id}

- Status: {$run->status}
- Template: {$run->template?->template_key}
- Output type: {$run->output_type}
- Total cost: \${$this->money($runCost)}
- Created: {$run->created_at}

## Providers

{$providers}

## Final Output

```json
{$run->final_output}
```

MD;
    }

    private function serviceProvider(string $moduleName, string $namespace, string $moduleSlug): string
    {
        return <<<PHP
<?php

namespace {$namespace}\\Providers;

use Illuminate\\Support\\ServiceProvider;

class {$moduleName}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \$modulePath = dirname(__DIR__);

        if (file_exists(\$modulePath . '/Config/config.php')) {
            \$this->mergeConfigFrom(\$modulePath . '/Config/config.php', '{$moduleSlug}');
        }
    }

    public function boot(): void
    {
        \$modulePath = dirname(__DIR__);

        if (file_exists(\$modulePath . '/Routes/web.php')) {
            \$this->loadRoutesFrom(\$modulePath . '/Routes/web.php');
        }

        if (file_exists(\$modulePath . '/Routes/api.php')) {
            \$this->loadRoutesFrom(\$modulePath . '/Routes/api.php');
        }

        \$this->loadViewsFrom(\$modulePath . '/Resources/views', '{$moduleSlug}');
        \$this->loadTranslationsFrom(\$modulePath . '/Resources/lang', '{$moduleSlug}');
        \$this->loadMigrationsFrom(\$modulePath . '/Database/Migrations');
    }
}
PHP;
    }

    private function webRoutes(string $moduleName, string $namespace, string $moduleSlug): string
    {
        return <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;
use {$namespace}\\Http\\Controllers\\{$moduleName}Controller;

Route::middleware(['web', 'auth'])
    ->prefix('{$moduleSlug}')
    ->name('{$moduleSlug}.')
    ->group(function () {
        Route::get('/', [{$moduleName}Controller::class, 'index'])->name('index');
    });
PHP;
    }

    private function apiRoutes(): string
    {
        return "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::middleware(['api'])->group(function () {\n    // API routes are intentionally left as a reviewed extension point.\n});\n";
    }

    private function controller(string $moduleName, string $namespace, string $moduleSlug): string
    {
        return <<<PHP
<?php

namespace {$namespace}\\Http\\Controllers;

use App\\Http\\Controllers\\Controller;
use Illuminate\\View\\View;

class {$moduleName}Controller extends Controller
{
    public function index(): View
    {
        return view('{$moduleSlug}::index');
    }
}
PHP;
    }

    private function service(string $moduleName, string $namespace): string
    {
        return <<<PHP
<?php

namespace {$namespace}\\Services;

class {$moduleName}Service
{
    public function health(): array
    {
        return [
            'status' => 'draft',
            'message' => 'Generated package awaiting human review.',
        ];
    }
}
PHP;
    }

    private function indexView(string $moduleName): string
    {
        return <<<BLADE
@extends(config('{$moduleName}.layout', 'layouts.app'))

@section('content')
<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">{$moduleName}</h1>
            <div class="text-muted">Generated LSG module draft.</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            Review the generated blueprint before enabling this module in production.
        </div>
    </div>
</div>
@endsection
BLADE;
    }

    private function json(array $payload): string
    {
        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) . "\n";
    }

    private function money(float $amount): string
    {
        return number_format($amount, 4, '.', '');
    }
}
