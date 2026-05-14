<?php

namespace Modules\DatabaseExplorer\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Modules\DatabaseExplorer\Services\DatabaseExplorerService;
use Modules\DatabaseExplorer\Services\DatabaseHealthService;
use Modules\DatabaseExplorer\Services\DatabaseSnapshotService;
use Modules\DatabaseExplorer\Services\MySqlMetadataProvider;
use Modules\DatabaseExplorer\Services\PostgresMetadataProvider;

class DatabaseExplorerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        $this->registerModuleConfig($modulePath);

        $this->app->singleton(DatabaseHealthService::class, fn () => new DatabaseHealthService());

        $this->app->singleton(PostgresMetadataProvider::class, function () {
            $connectionName = config('database-explorer.connection');
            $driver = config('database-explorer.driver') ?: config("database.connections.{$connectionName}.driver");

            if ($driver === 'mysql') {
                return new MySqlMetadataProvider(
                    connectionName: $connectionName,
                    allowedSchemas: env('DB_EXPLORER_ALLOWED_SCHEMAS') ? (array) config('database-explorer.allowed_schemas', []) : [config('database.connections.' . $connectionName . '.database')],
                    excludedSchemas: (array) config('database-explorer.mysql_excluded_schemas', ['information_schema', 'mysql', 'performance_schema', 'sys'])
                );
            }

            return new PostgresMetadataProvider(
                connectionName: $connectionName,
                allowedSchemas: (array) config('database-explorer.allowed_schemas', []),
                excludedSchemas: (array) config('database-explorer.excluded_schemas', ['information_schema', 'pg_catalog', 'pg_toast'])
            );
        });

        $this->app->singleton(DatabaseExplorerService::class, function ($app) {
            return new DatabaseExplorerService(
                metadataProvider: $app->make(PostgresMetadataProvider::class),
                healthService: $app->make(DatabaseHealthService::class)
            );
        });

        $this->app->singleton(DatabaseSnapshotService::class, function ($app) {
            return new DatabaseSnapshotService($app->make(DatabaseExplorerService::class));
        });
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        if ((bool) config('database-explorer.enabled', true)) {
            $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        }

        $this->loadViewsFrom($modulePath . '/Resources/views', 'database-explorer');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'database-explorer');
    }

    protected function registerModuleConfig(string $modulePath): void
    {
        $mainConfigPath = $modulePath . '/Config/config.php';
        $uiConfigPath = $modulePath . '/Config/ui.php';

        $mainConfig = file_exists($mainConfigPath) ? require $mainConfigPath : [];
        $uiConfig = file_exists($uiConfigPath) ? require $uiConfigPath : [];

        foreach (['database-explorer', 'database_explorer', 'databaseExplorer'] as $key) {
            if (file_exists($mainConfigPath)) {
                $this->mergeConfigFrom($mainConfigPath, $key);
            }

            $existing = (array) config($key, []);
            $merged = array_replace_recursive($mainConfig, $existing);

            if (empty(Arr::get($merged, 'health')) && ! empty($mainConfig['health'])) {
                $merged['health'] = $mainConfig['health'];
            }

            if (! empty($uiConfig)) {
                $merged['ui'] = array_replace_recursive($uiConfig, (array) Arr::get($merged, 'ui', []));
            }

            config([$key => $merged]);
        }
    }
}
