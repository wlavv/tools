<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\PermissionRoleManager\Services\RoutePermissionAccessService;
use Throwable;

class settingsController extends Controller{

    protected bool $hasPageActions = false;

    public function index(){
        $this->addRouteAccess('system-tools.index', __('app::areas.settings.system_maintenance'), 'fa-file-code');
        $this->addRouteAccess('system_logs.index', __('app::areas.settings.system_logs'), 'fa-file-lines');

        $this->addRouteAccess('config_inspector.index', __('app::areas.settings.config_inspector'), 'fa-solid fa-sliders');
        $this->addRouteAccess('environment_manager.index', __('app::areas.settings.environment_manager'), 'fa-solid fa-leaf');
        $this->addRouteAccess('database_explorer.index', __('app::areas.settings.database_explorer'), 'fa-solid fa-database');
        $this->addRouteAccess('module_health.index', __('app::areas.settings.module_health'), 'fa-solid fa-heart-pulse');
        $this->addRouteAccess('module-dependency-map.index', __('app::areas.settings.module_dependency_map'), 'fa-solid fa-diagram-project');
        $this->addRouteAccess('integration_health.index', __('app::areas.settings.integration_health'), 'fa-solid fa-plug-circle-check');
        $this->addRouteAccess('error-center.index', __('app::areas.settings.error_center'), 'fa-solid fa-triangle-exclamation');
        $this->addRouteAccess('audit_log_central.dashboard', __('app::areas.settings.audit_log_central'), 'fa-solid fa-clipboard-list');
        $this->addRouteAccess('job_queue_monitor.index', __('app::areas.settings.job_queue_monitor'), 'fa-solid fa-list-check');
        $this->addRouteAccess('streamdeck_access.index', __('app::areas.settings.streamdeck_access'), 'fa-solid fa-table-cells-large');
        $this->addRouteAccess('translation_manager.index', __('app::areas.settings.translation_manager'), 'fa-solid fa-language');
        $this->addRouteAccess('data_import_wizard.dashboard', __('app::areas.settings.data_import_wizard'), 'fa-solid fa-file-import');
        $this->addRouteAccess('data_export_center.dashboard', __('app::areas.settings.data_export_center'), 'fa-solid fa-file-export');
        $this->addRouteAccess('permission_role_manager.dashboard', __('app::areas.settings.permission_role_manager'), 'fa-solid fa-user-shield');

        return $this->view('areas/settings/index', [
            'focusCards' => $this->settingsFocusCards(),
        ]);
    }

    private function settingsFocusCards(): array
    {
        return array_values(array_filter([
            $this->focusCard(
                'error-center.index',
                __('app::areas.settings.error_center'),
                'fa-solid fa-bug',
                $this->countWhere('error_events', fn ($query) => $query
                    ->whereNotIn('status', ['resolved', 'ignored'])
                    ->whereIn('severity', ['critical', 'fatal', 'error'])),
                'Erros abertos criticos ou por resolver',
                'critical',
                ['status' => 'open']
            ),
            $this->focusCard(
                'integration_health.events.index',
                __('app::areas.settings.integration_health'),
                'fa-solid fa-plug-circle-xmark',
                $this->countWhere('integration_health_events', fn ($query) => $query
                    ->whereNull('resolved_at')
                    ->whereIn('severity', ['critical', 'fatal', 'error'])),
                'Eventos de integracao abertos',
                'critical',
                ['status' => 'open']
            ),
            $this->focusCard(
                'job_queue_monitor.failed.index',
                __('app::areas.settings.job_queue_monitor'),
                'fa-solid fa-triangle-exclamation',
                $this->countWhere('job_queue_monitor_runs', fn ($query) => $query
                    ->where('status', 'failed')
                    ->whereNull('resolved_at')),
                'Jobs falhados por resolver',
                'critical'
            ),
            $this->focusCard(
                'module_health.modules.index',
                __('app::areas.settings.module_health'),
                'fa-solid fa-heart-crack',
                $this->latestModuleHealthIssues(),
                'Modulos broken ou incomplete no ultimo scan',
                'warning'
            ),
            $this->focusCard(
                'module-dependency-map.index',
                __('app::areas.settings.module_dependency_map'),
                'fa-solid fa-diagram-project',
                $this->countWhere('module_dependency_scans', fn ($query) => $query
                    ->where('status', 'completed')
                    ->where(function ($nested) {
                        $nested->where('health_status', 'critical')
                            ->orWhere('circular_dependencies_count', '>', 0)
                            ->orWhere('stale_dependencies_count', '>', 0);
                    })),
                'Dependencias criticas, circulares ou stale',
                'warning'
            ),
            $this->focusCard(
                'data_import_wizard.batches.index',
                __('app::areas.settings.data_import_wizard'),
                'fa-solid fa-file-circle-exclamation',
                $this->countWhere('data_import_batches', fn ($query) => $query
                    ->where(function ($nested) {
                        $nested->whereIn('status', ['failed', 'error', 'completed_with_errors', 'preview_with_errors'])
                            ->orWhere('error_rows', '>', 0);
                    })),
                'Imports com erros ou linhas invalidas',
                'warning'
            ),
            $this->focusCard(
                'data_export_center.dashboard',
                __('app::areas.settings.data_export_center'),
                'fa-solid fa-file-export',
                $this->countWhere('data_export_batches', fn ($query) => $query
                    ->whereIn('status', ['failed', 'error'])),
                'Exports falhados',
                'warning'
            ),
            $this->focusCard(
                'permission_role_manager.route_access.index',
                __('app::areas.settings.permission_role_manager'),
                'fa-solid fa-user-shield',
                $this->countWhere('permission_roles', fn ($query) => $query
                    ->where('is_system', true)
                    ->whereNull('deleted_at')),
                'Perfis automaticos ainda visiveis',
                'info'
            ),
            $this->focusCard(
                'streamdeck_access.index',
                __('app::areas.settings.streamdeck_access'),
                'fa-solid fa-table-cells-large',
                $this->countWhere('streamdeck_access_logs', fn ($query) => $query
                    ->whereIn('status', ['failed', 'rejected', 'error'])),
                'Triggers falhados ou rejeitados',
                'warning'
            ),
            $this->focusCard(
                'database_explorer.health',
                __('app::areas.settings.database_explorer'),
                'fa-solid fa-database',
                $this->countWhere('database_explorer_findings', fn ($query) => $query
                    ->whereIn('severity', ['critical', 'warning'])),
                'Findings tecnicos da base de dados',
                'info'
            ),
            $this->focusCard(
                'audit_log_central.index',
                __('app::areas.settings.audit_log_central'),
                'fa-solid fa-clipboard-list',
                $this->countWhere('audit_logs', fn ($query) => $query
                    ->whereIn('severity', ['critical', 'error', 'warning'])
                    ->where('occurred_at', '>=', now()->subDays(7))),
                'Eventos de auditoria relevantes nos ultimos 7 dias',
                'info'
            ),
        ]));
    }

    private function focusCard(string $routeName, string $title, string $icon, int $count, string $description, string $severity = 'info', array $params = []): ?array
    {
        if (!Route::has($routeName)) {
            return null;
        }

        $allowed = app(RoutePermissionAccessService::class)
            ->canAccessRouteName(auth()->id(), $routeName);

        if (!$allowed) {
            return null;
        }

        return [
            'title' => $title,
            'icon' => $icon,
            'count' => $count,
            'description' => $description,
            'severity' => $count > 0 ? $severity : 'ok',
            'url' => route($routeName, $params),
        ];
    }

    private function latestModuleHealthIssues(): int
    {
        try {
            if (!Schema::hasTable('module_health_scans') || !Schema::hasTable('module_health_scan_items')) {
                return 0;
            }

            $latestScanId = DB::table('module_health_scans')->latest('id')->value('id');

            if (!$latestScanId) {
                return 0;
            }

            return (int) DB::table('module_health_scan_items')
                ->where('scan_id', $latestScanId)
                ->whereIn('status', ['broken', 'incomplete'])
                ->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function countWhere(string $table, callable $callback): int
    {
        try {
            if (!Schema::hasTable($table)) {
                return 0;
            }

            $query = DB::table($table);
            $callback($query);

            return (int) $query->count();
        } catch (Throwable) {
            return 0;
        }
    }
}
