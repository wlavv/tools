<?php

namespace Modules\EnvironmentManager\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\EnvironmentManager\Support\ArrayTools;
use Modules\EnvironmentManager\Support\EntryFilter;
use Modules\EnvironmentManager\Support\SensitiveValueMasker;
use Throwable;

class EnvironmentManagerService
{
    protected SensitiveValueMasker $masker;

    public function __construct(?SensitiveValueMasker $masker = null)
    {
        $this->masker = $masker ?: new SensitiveValueMasker();
    }

    public function overview(): array
    {
        $envFilePath = $this->envFilePath();
        $modules = $this->moduleConfigs();
        $envFileEntries = $this->envFileEntries();
        $runtimeEntries = $this->runtimeEnvEntries();
        $configEntries = $this->laravelConfigEntries();

        return [
            'app_name' => config('app.name'),
            'app_env' => app()->environment(),
            'app_debug' => (bool) config('app.debug'),
            'app_url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'php_version' => PHP_VERSION,
            'laravel_version' => method_exists(app(), 'version') ? app()->version() : null,
            'base_path' => base_path(),
            'modules_path' => $this->modulesPath(),
            'env_file_path' => $envFilePath,
            'env_file_exists' => file_exists($envFilePath),
            'env_file_readable' => is_readable($envFilePath),
            'config_cached' => method_exists(app(), 'configurationIsCached') ? app()->configurationIsCached() : false,
            'readonly' => true,
            'counts' => [
                'env_file' => count($envFileEntries),
                'runtime_env' => count($runtimeEntries),
                'laravel_config' => count($configEntries),
                'modules' => count($modules),
                'module_config_entries' => array_sum(array_map(fn (array $module) => count($module['configs'] ?? []), $modules)),
            ],
        ];
    }

    public function envFileEntries(?string $search = null): array
    {
        $path = $this->envFilePath();

        if (! is_readable($path)) {
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            return [];
        }

        $entries = [];

        foreach ($lines as $lineNumber => $line) {
            $parsed = $this->parseEnvLine((string) $line);

            if ($parsed === null) {
                continue;
            }

            [$key, $value] = $parsed;
            $entries[] = $this->entry($key, $value, '.env', [
                'location' => config('environment-manager.env_file', '.env'),
                'line' => $lineNumber + 1,
            ]);
        }

        return EntryFilter::filter($entries, $search);
    }

    public function runtimeEnvEntries(?string $search = null): array
    {
        $runtime = getenv();
        $values = is_array($runtime) ? $runtime : [];

        foreach ($_ENV as $key => $value) {
            $values[(string) $key] = $value;
        }

        if ((bool) config('environment-manager.runtime_env.include_server', false)) {
            foreach ($_SERVER as $key => $value) {
                $values[(string) $key] = $value;
            }
        }

        foreach ((array) config('environment-manager.runtime_env.deny_keys', []) as $denyKey) {
            unset($values[(string) $denyKey]);
        }

        ksort($values, SORT_NATURAL | SORT_FLAG_CASE);

        $entries = [];

        foreach ($values as $key => $value) {
            $entries[] = $this->entry((string) $key, $value, 'runtime_env');
        }

        return EntryFilter::filter($entries, $search);
    }

    public function laravelConfigEntries(?string $search = null): array
    {
        $flattened = ArrayTools::dotPreserveEmpty((array) app('config')->all());
        ksort($flattened, SORT_NATURAL | SORT_FLAG_CASE);

        $entries = [];

        foreach ($flattened as $key => $value) {
            $entries[] = $this->entry((string) $key, $value, 'laravel_config');
        }

        return EntryFilter::filter($entries, $search);
    }

    public function moduleConfigs(?string $search = null): array
    {
        $modules = $this->filesystemModuleConfigs($search);

        foreach ($this->databaseModuleConfigs($search) as $key => $databaseModule) {
            if (! isset($modules[$key])) {
                $modules[$key] = $databaseModule;
                continue;
            }

            $modules[$key]['configs'] = array_values(array_merge($modules[$key]['configs'], $databaseModule['configs']));
            $modules[$key]['sources'] = array_values(array_unique(array_merge($modules[$key]['sources'], $databaseModule['sources'])));
            $modules[$key]['source'] = implode(', ', $modules[$key]['sources']);
        }

        uasort($modules, fn (array $a, array $b) => strnatcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

        return array_values($modules);
    }

    public function moduleConfig(string $moduleKey): ?array
    {
        $wanted = $this->normalizeModuleKey($moduleKey);

        foreach ($this->moduleConfigs() as $module) {
            $candidates = array_filter([
                $module['key'] ?? null,
                $module['slug'] ?? null,
                $module['folder'] ?? null,
                $module['name'] ?? null,
            ]);

            foreach ($candidates as $candidate) {
                if ($this->normalizeModuleKey((string) $candidate) === $wanted) {
                    return $module;
                }
            }
        }

        return null;
    }

    public function effectiveConfig(?string $search = null): array
    {
        $envFile = $this->keyBy($this->envFileEntries(), 'key');
        $runtime = $this->keyBy($this->runtimeEnvEntries(), 'key');
        $envKeys = array_unique(array_merge(array_keys($envFile), array_keys($runtime)));
        natcasesort($envKeys);

        $effectiveEnv = [];

        foreach ($envKeys as $key) {
            $resolved = $runtime[$key] ?? $envFile[$key] ?? null;

            if ($resolved === null) {
                continue;
            }

            $overrides = [];

            if (isset($envFile[$key])) {
                $overrides[] = [
                    'source' => '.env',
                    'value' => $envFile[$key]['value'],
                    'location' => $envFile[$key]['location'] ?? null,
                    'line' => $envFile[$key]['line'] ?? null,
                ];
            }

            if (isset($runtime[$key])) {
                $overrides[] = [
                    'source' => 'runtime_env',
                    'value' => $runtime[$key]['value'],
                    'location' => null,
                    'line' => null,
                ];
            }

            $entry = $resolved;
            $entry['source'] = isset($runtime[$key]) ? 'runtime_env' : '.env';
            $entry['overrides'] = $overrides;
            $effectiveEnv[] = $entry;
        }

        return [
            'env' => EntryFilter::filter($effectiveEnv, $search),
            'config' => $this->laravelConfigEntries($search),
        ];
    }

    protected function filesystemModuleConfigs(?string $search = null): array
    {
        if (! (bool) config('environment-manager.scan_module_config_files', true)) {
            return [];
        }

        $modulesPath = $this->modulesPath();

        if (! is_dir($modulesPath)) {
            return [];
        }

        $directories = glob($modulesPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
        sort($directories, SORT_NATURAL | SORT_FLAG_CASE);

        $modules = [];

        foreach ($directories as $directory) {
            $folder = basename($directory);
            $manifest = $this->readModuleManifest($directory);
            $slug = (string) ($manifest['slug'] ?? Str::kebab($folder));
            $name = (string) ($manifest['name'] ?? $folder);
            $configs = [];

            foreach ($this->moduleConfigFiles($directory) as $configFile) {
                $fileConfig = $this->safeRequireArray($configFile);

                if ($fileConfig === null) {
                    continue;
                }

                $fileKey = pathinfo($configFile, PATHINFO_FILENAME);
                $flattened = ArrayTools::dotPreserveEmpty($fileConfig);
                ksort($flattened, SORT_NATURAL | SORT_FLAG_CASE);

                foreach ($flattened as $configKey => $value) {
                    $entryKey = $fileKey === 'config'
                        ? (string) $configKey
                        : $fileKey . '.' . $configKey;

                    $configs[] = $this->entry($entryKey, $value, 'module_config', [
                        'module' => $slug,
                        'location' => $this->relativePath($configFile),
                    ]);
                }
            }

            $filteredConfigs = EntryFilter::filter($configs, $search);
            $moduleMatches = EntryFilter::matches([
                'key' => $slug,
                'name' => $name,
                'folder' => $folder,
                'description' => $manifest['description'] ?? null,
                'provider' => $manifest['provider'] ?? null,
            ], $search);

            if ($this->hasSearch($search) && ! $moduleMatches && count($filteredConfigs) === 0) {
                continue;
            }

            $modules[$slug] = [
                'key' => $slug,
                'slug' => $slug,
                'folder' => $folder,
                'name' => $name,
                'enabled' => (bool) ($manifest['enabled'] ?? true),
                'version' => $manifest['version'] ?? null,
                'description' => $manifest['description'] ?? null,
                'provider' => $manifest['provider'] ?? null,
                'source' => 'filesystem',
                'sources' => ['filesystem'],
                'manifest' => $manifest,
                'configs' => $this->hasSearch($search) && ! $moduleMatches ? $filteredConfigs : $configs,
            ];
        }

        return $modules;
    }

    protected function databaseModuleConfigs(?string $search = null): array
    {
        if (! (bool) config('environment-manager.bo_module_configs.enabled', true)) {
            return [];
        }

        $modules = [];

        foreach ((array) config('environment-manager.bo_module_configs.metadata_tables', []) as $table) {
            $this->collectDatabaseMetadataModules($modules, (string) $table, $search);
        }

        foreach ((array) config('environment-manager.bo_module_configs.config_tables', []) as $table) {
            $this->collectDatabaseConfigRows($modules, (string) $table, $search);
        }

        foreach ($modules as $key => $module) {
            $filteredConfigs = EntryFilter::filter($module['configs'], $search);
            $moduleMatches = EntryFilter::matches([
                'key' => $module['key'] ?? null,
                'name' => $module['name'] ?? null,
                'description' => $module['description'] ?? null,
            ], $search);

            if ($this->hasSearch($search) && ! $moduleMatches && count($filteredConfigs) === 0) {
                unset($modules[$key]);
                continue;
            }

            if ($this->hasSearch($search) && ! $moduleMatches) {
                $modules[$key]['configs'] = $filteredConfigs;
            }
        }

        return $modules;
    }

    protected function collectDatabaseMetadataModules(array &$modules, string $table, ?string $search = null): void
    {
        if ($table === '') {
            return;
        }

        try {
            if (! Schema::hasTable($table)) {
                return;
            }

            $columns = Schema::getColumnListing($table);
            $rows = DB::table($table)->limit((int) config('environment-manager.bo_module_configs.rows_limit', 500))->get();
        } catch (Throwable) {
            return;
        }

        $moduleKeyColumn = $this->firstExistingColumn((array) config('environment-manager.bo_module_configs.module_key_columns', []), $columns);
        $moduleNameColumn = $this->firstExistingColumn((array) config('environment-manager.bo_module_configs.module_name_columns', []), $columns);
        $enabledColumn = $this->firstExistingColumn((array) config('environment-manager.bo_module_configs.enabled_columns', []), $columns);
        $descriptionColumn = $this->firstExistingColumn((array) config('environment-manager.bo_module_configs.description_columns', []), $columns);
        $jsonColumns = array_values(array_intersect((array) config('environment-manager.bo_module_configs.json_columns', []), $columns));

        foreach ($rows as $row) {
            $rowArray = (array) $row;
            $moduleKey = $this->moduleKeyFromRow($rowArray, $moduleKeyColumn);
            $name = $this->columnValue($rowArray, $moduleNameColumn) ?: $moduleKey;

            $module = $this->baseDatabaseModule(
                key: $moduleKey,
                name: (string) $name,
                source: 'database:' . $table,
                enabled: $this->enabledFromValue($this->columnValue($rowArray, $enabledColumn)),
                description: $this->columnValue($rowArray, $descriptionColumn)
            );

            foreach ($jsonColumns as $jsonColumn) {
                foreach ($this->flattenDatabaseJsonColumn($rowArray[$jsonColumn] ?? null) as $configKey => $value) {
                    $module['configs'][] = $this->entry((string) $configKey, $value, 'database_module_config', [
                        'module' => $moduleKey,
                        'location' => $table . '.' . $jsonColumn,
                    ]);
                }
            }

            $this->mergeDatabaseModule($modules, $module);
        }
    }

    protected function collectDatabaseConfigRows(array &$modules, string $table, ?string $search = null): void
    {
        if ($table === '') {
            return;
        }

        try {
            if (! Schema::hasTable($table)) {
                return;
            }

            $columns = Schema::getColumnListing($table);
            $rows = DB::table($table)->limit((int) config('environment-manager.bo_module_configs.rows_limit', 500))->get();
        } catch (Throwable) {
            return;
        }

        $moduleKeyColumn = $this->firstExistingColumn((array) config('environment-manager.bo_module_configs.module_key_columns', []), $columns);
        $moduleNameColumn = $this->firstExistingColumn((array) config('environment-manager.bo_module_configs.module_name_columns', []), $columns);
        $configKeyColumn = $this->firstExistingColumn((array) config('environment-manager.bo_module_configs.config_key_columns', []), $columns);
        $valueColumn = $this->firstExistingColumn((array) config('environment-manager.bo_module_configs.value_columns', []), $columns);
        $typeColumn = $this->firstExistingColumn((array) config('environment-manager.bo_module_configs.type_columns', []), $columns);
        $sensitiveColumn = $this->firstExistingColumn((array) config('environment-manager.bo_module_configs.sensitive_columns', []), $columns);
        $descriptionColumn = $this->firstExistingColumn((array) config('environment-manager.bo_module_configs.description_columns', []), $columns);
        $enabledColumn = $this->firstExistingColumn((array) config('environment-manager.bo_module_configs.enabled_columns', []), $columns);
        $jsonColumns = array_values(array_intersect((array) config('environment-manager.bo_module_configs.json_columns', []), $columns));

        foreach ($rows as $row) {
            $rowArray = (array) $row;
            $moduleKey = $this->moduleKeyFromRow($rowArray, $moduleKeyColumn);
            $name = $this->columnValue($rowArray, $moduleNameColumn) ?: $moduleKey;
            $module = $this->baseDatabaseModule(
                key: $moduleKey,
                name: (string) $name,
                source: 'database:' . $table,
                enabled: $this->enabledFromValue($this->columnValue($rowArray, $enabledColumn)),
                description: null
            );

            if ($configKeyColumn && $valueColumn) {
                $configKey = $this->columnValue($rowArray, $configKeyColumn);

                if ($configKey !== null && $configKey !== '') {
                    $module['configs'][] = $this->entry((string) $configKey, $this->columnValue($rowArray, $valueColumn), 'database_module_config', [
                        'module' => $moduleKey,
                        'location' => $table . '.' . $valueColumn,
                        'declared_type' => $this->columnValue($rowArray, $typeColumn),
                        'description' => $this->columnValue($rowArray, $descriptionColumn),
                        'sensitive' => $this->booleanOrNull($this->columnValue($rowArray, $sensitiveColumn)),
                    ]);
                }
            }

            foreach ($jsonColumns as $jsonColumn) {
                foreach ($this->flattenDatabaseJsonColumn($rowArray[$jsonColumn] ?? null) as $configKey => $value) {
                    $module['configs'][] = $this->entry((string) $configKey, $value, 'database_module_config', [
                        'module' => $moduleKey,
                        'location' => $table . '.' . $jsonColumn,
                    ]);
                }
            }

            $this->mergeDatabaseModule($modules, $module);
        }
    }

    protected function baseDatabaseModule(string $key, string $name, string $source, bool $enabled = true, mixed $description = null): array
    {
        return [
            'key' => $key,
            'slug' => $key,
            'folder' => null,
            'name' => $name,
            'enabled' => $enabled,
            'version' => null,
            'description' => is_scalar($description) ? (string) $description : null,
            'provider' => null,
            'source' => $source,
            'sources' => [$source],
            'manifest' => [],
            'configs' => [],
        ];
    }

    protected function mergeDatabaseModule(array &$modules, array $module): void
    {
        $key = $module['key'];

        if (! isset($modules[$key])) {
            $modules[$key] = $module;
            return;
        }

        $modules[$key]['configs'] = array_values(array_merge($modules[$key]['configs'], $module['configs']));
        $modules[$key]['sources'] = array_values(array_unique(array_merge($modules[$key]['sources'], $module['sources'])));
        $modules[$key]['source'] = implode(', ', $modules[$key]['sources']);
    }

    protected function moduleConfigFiles(string $directory): array
    {
        $files = glob($directory . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . '*.php') ?: [];
        sort($files, SORT_NATURAL | SORT_FLAG_CASE);

        return $files;
    }

    protected function readModuleManifest(string $directory): array
    {
        $path = $directory . DIRECTORY_SEPARATOR . 'module.json';

        if (! is_readable($path)) {
            return [];
        }

        $json = file_get_contents($path);

        if ($json === false) {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function safeRequireArray(string $path): ?array
    {
        try {
            $value = require $path;
        } catch (Throwable) {
            return null;
        }

        return is_array($value) ? $value : null;
    }

    protected function parseEnvLine(string $line): ?array
    {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            return null;
        }

        if (str_starts_with($line, 'export ')) {
            $line = trim(substr($line, 7));
        }

        $position = strpos($line, '=');

        if ($position === false) {
            return null;
        }

        $key = trim(substr($line, 0, $position));
        $value = trim(substr($line, $position + 1));

        if ($key === '') {
            return null;
        }

        $first = substr($value, 0, 1);
        $last = substr($value, -1);

        if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
            $value = substr($value, 1, -1);
        } else {
            $value = preg_replace('/\s+#.*$/', '', $value) ?? $value;
        }

        return [$key, $this->normalizeEnvValue($value)];
    }

    protected function normalizeEnvValue(string $value): mixed
    {
        $trimmed = trim($value);
        $lower = strtolower($trimmed);

        return match ($lower) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => is_numeric($trimmed) ? $this->numericValue($trimmed) : $trimmed,
        };
    }

    protected function numericValue(string $value): int|float
    {
        return str_contains($value, '.') ? (float) $value : (int) $value;
    }

    protected function flattenDatabaseJsonColumn(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return ArrayTools::dotPreserveEmpty($value);
        }

        if (is_object($value)) {
            return ArrayTools::dotPreserveEmpty((array) $value);
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return ArrayTools::dotPreserveEmpty($decoded);
            }

            return ['value' => $value];
        }

        return ['value' => $value];
    }

    protected function entry(string $key, mixed $value, string $source, array $extra = []): array
    {
        return $this->masker->entry($key, $value, $source, array_merge([
            'module' => null,
            'location' => null,
            'line' => null,
        ], $extra));
    }

    protected function keyBy(array $entries, string $key): array
    {
        $result = [];

        foreach ($entries as $entry) {
            if (array_key_exists($key, $entry)) {
                $result[(string) $entry[$key]] = $entry;
            }
        }

        return $result;
    }

    protected function firstExistingColumn(array $candidates, array $columns): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return (string) $candidate;
            }
        }

        return null;
    }

    protected function columnValue(array $row, ?string $column): mixed
    {
        if ($column === null || ! array_key_exists($column, $row)) {
            return null;
        }

        return $row[$column];
    }

    protected function moduleKeyFromRow(array $row, ?string $column): string
    {
        $value = $this->columnValue($row, $column);

        if ($value === null || $value === '') {
            $value = $row['id'] ?? 'module';
        }

        return $this->normalizeModuleKey((string) $value);
    }

    protected function normalizeModuleKey(string $value): string
    {
        return Str::kebab(trim($value));
    }

    protected function booleanOrNull(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return in_array(Str::lower((string) $value), ['1', 'true', 'yes', 'sim', 'sensitive', 'secret'], true);
    }

    protected function enabledFromValue(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return ! in_array(Str::lower((string) $value), ['0', 'false', 'no', 'não', 'disabled', 'inactive', 'inativo'], true);
    }

    protected function modulesPath(): string
    {
        $path = (string) config('environment-manager.modules_path', 'Modules');

        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return base_path($path);
    }

    protected function envFilePath(): string
    {
        $path = (string) config('environment-manager.env_file', '.env');

        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return base_path($path);
    }

    protected function relativePath(string $path): string
    {
        $basePath = rtrim(base_path(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (str_starts_with($path, $basePath)) {
            return substr($path, strlen($basePath));
        }

        return $path;
    }

    protected function hasSearch(?string $search): bool
    {
        return trim((string) $search) !== '';
    }
}
