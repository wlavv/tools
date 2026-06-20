<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\LSG\SiteManager\Models\Site;
use Modules\LSG\SiteManager\Services\SitePageSpeedInsightsService;
use Modules\PermissionRoleManager\Services\RoutePermissionAccessService;

class lsgController extends Controller
{
    public function index(SitePageSpeedInsightsService $pageSpeed)
    {
        $this->prepareLsgPage('LSG', false);
        $this->addRouteAccess('lsg.infrastructure', 'Infraestrutura', 'fa-solid fa-sitemap');
        $this->addRouteAccess('lsg.stores', 'Stores', 'fa-solid fa-store');
        $this->addRouteAccess('lsg.services', 'Servicos LSG', 'fa-solid fa-briefcase');
        $this->addRouteAccess('lsg.reporting', 'Reporting', 'fa-solid fa-chart-line');

        return $this->sitesDashboard($pageSpeed);
    }

    public function infrastructure()
    {
        $this->prepareLsgPage('Infraestrutura LSG');

        $this->addRouteAccess('lsg.site_manager.dashboard', 'Sites', 'fa-solid fa-globe');
        $this->addRouteAccess('admin.infrastructure.ai-backups.index', 'AI Backups', 'fa-solid fa-server');
        $this->addRouteAccess('admin.lsg-ai.index', 'LSG AI Gateway', 'fa-solid fa-brain');
        $this->addRouteAccess('admin.infrastructure.documentation.index', 'Documentacao', 'fa-solid fa-book');
        $this->addRouteAccess('system-tools.index', 'System Tools', 'fa-solid fa-screwdriver-wrench');

        return $this->view('areas/lsg/infrastructure');
    }

    public function stores()
    {
        $this->prepareLsgPage('Stores LSG');
        $this->addRouteAccess('product_growth.product_core.dashboard', 'Product Growth', 'fa-solid fa-diagram-project');
        $this->addRouteAccess('multiStore.index', 'MultiStore', 'fa-solid fa-store');

        return $this->view('areas/lsg/stores');
    }

    public function services()
    {
        $this->prepareLsgPage('Servicos LSG');
        $this->addRouteAccess('package_tracker.dashboard', 'Tracking', 'fa-solid fa-truck-fast');
        $this->addRouteAccess('package_tracker.shipments.index', 'Trackings', 'fa-solid fa-boxes-packing');
        $this->addRouteAccess('package_tracker.clients.index', 'Clientes tracking', 'fa-solid fa-users');
        $this->addRouteAccess('webcatalogue.index', 'WebCatalogue', 'fa-solid fa-book-open');
        $this->addRouteAccess('document-manager.dashboard', 'Document Manager', 'fa-solid fa-folder-tree');
        $this->addRouteAccess('data_import_wizard.dashboard', 'Data Import Wizard', 'fa-solid fa-file-import');
        $this->addRouteAccess('data_export_center.dashboard', 'Data Export Center', 'fa-solid fa-file-export');

        return $this->view('areas/lsg/services');
    }

    public function reporting()
    {
        $this->prepareLsgPage('Reporting LSG');
        $this->addRouteAccess('lsg.site_manager.dashboard', 'PageSpeed diário', 'fa-solid fa-gauge-high');
        $this->addRouteAccess('integration_health.dashboard', 'Integration Health', 'fa-solid fa-plug-circle-check');
        $this->addRouteAccess('audit_log_central.dashboard', 'Audit Log', 'fa-solid fa-clipboard-list');

        return $this->view('areas/lsg/reporting');
    }

    private function sitesDashboard(SitePageSpeedInsightsService $pageSpeed)
    {
        $sites = Schema::hasTable('lsg_sites')
            ? Site::query()
                ->orderBy('name')
                ->get()
            : collect();

        $sites = $this->attachProjectUrls($sites);

        $groups = collect([
            'store' => ['label' => 'Lojas', 'icon' => 'fa-solid fa-store', 'description' => 'Lojas e canais comerciais do grupo.'],
            'service' => ['label' => 'Servicos', 'icon' => 'fa-solid fa-briefcase', 'description' => 'Sites de servicos e ferramentas externas.'],
            'presentation' => ['label' => 'Capa / apresentacao', 'icon' => 'fa-solid fa-display', 'description' => 'Sites institucionais, capas e apresentacoes.'],
        ])->map(function (array $meta, string $key) use ($sites) {
            $items = $sites->where('site_type', $key)->values();

            return array_merge($meta, [
                'key' => $key,
                'count' => $items->count(),
                'active' => $items->where('status', 'active')->count(),
                'sites' => $items,
            ]);
        });

        return $this->view('areas/lsg/index', [
            'sites' => $sites,
            'groups' => $groups,
            'pageSpeedMetrics' => $pageSpeed->todayMetricsForSites($sites),
            'pageSpeedMetricsByStrategy' => [
                'mobile' => $pageSpeed->todayMetricsForSites($sites, 'mobile'),
                'desktop' => $pageSpeed->todayMetricsForSites($sites, 'desktop'),
            ],
        ]);
    }

    private function prepareLsgPage(string $title, bool $withBackAction = true): void
    {
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index'), 'translate' => false],
            ['label' => 'LSG', 'url' => $withBackAction ? route('lsg.index') : null, 'translate' => false],
        ];

        if ($withBackAction) {
            $breadcrumbs[] = ['label' => $title, 'url' => null, 'translate' => false];
        }

        $this->setPageTitle($title);
        $this->setBreadcrumbs($breadcrumbs);
        $this->setActions([]);

        if (!$withBackAction) {
            return;
        }

        $this->addAction([
            'key' => 'back_lsg',
            'label' => 'LSG',
            'name' => 'LSG',
            'icon' => 'fa-solid fa-angle-left',
            'route' => 'lsg.index',
            'url' => route('lsg.index'),
            'type' => 'link',
            'class' => 'lsg-action-btn lsg-action-btn--back',
        ]);
    }

    private function attachProjectUrls($sites)
    {
        if ($sites->isEmpty() || !Route::has('project_manager.projects.show') || !Schema::hasTable('wt_projects')) {
            return $sites;
        }

        $canOpenProjects = app(RoutePermissionAccessService::class)
            ->canAccessRouteName(auth()->id(), 'project_manager.projects.show');

        if (!$canOpenProjects) {
            return $sites;
        }

        $projectColumns = Schema::getColumnListing('wt_projects');
        $urlColumns = array_values(array_intersect($projectColumns, ['website', 'url', 'production_url', 'staging_url']));
        $selectColumns = array_values(array_unique(array_merge(['id', 'name', 'slug', 'code'], $urlColumns)));

        $projects = DB::table('wt_projects')
            ->select(array_values(array_intersect($selectColumns, $projectColumns)))
            ->get();

        $projectsById = $projects->keyBy('id');
        $projectsByKey = [];

        foreach ($projects as $project) {
            foreach (['name', 'slug', 'code'] as $column) {
                if (!empty($project->{$column})) {
                    $projectsByKey[$this->normaliseProjectKey($project->{$column})] = $project;
                }
            }

            foreach ($urlColumns as $column) {
                if (!empty($project->{$column})) {
                    $projectsByKey[$this->normaliseDomainKey($project->{$column})] = $project;
                }
            }
        }

        return $sites->map(function ($site) use ($projectsById, $projectsByKey) {
            $project = $this->resolveSiteProject($site, $projectsById, $projectsByKey);
            $site->project_url = $project ? route('project_manager.projects.show', $project->id) : null;
            $site->project_name = $project->name ?? null;

            return $site;
        });
    }

    private function resolveSiteProject(object $site, $projectsById, array $projectsByKey): ?object
    {
        foreach (['project_id', 'id_project'] as $column) {
            if (!empty($site->{$column}) && $projectsById->has((int) $site->{$column})) {
                return $projectsById->get((int) $site->{$column});
            }
        }

        $settings = is_array($site->settings ?? null)
            ? $site->settings
            : json_decode((string) ($site->settings ?? ''), true);

        if (is_array($settings)) {
            foreach (['project_id', 'id_project'] as $key) {
                if (!empty($settings[$key]) && is_scalar($settings[$key]) && $projectsById->has((int) $settings[$key])) {
                    return $projectsById->get((int) $settings[$key]);
                }
            }
        }

        foreach ([$site->domain ?? null, $site->code ?? null, $site->name ?? null] as $value) {
            if (!is_scalar($value) || !$value) {
                continue;
            }

            $domainKey = $this->normaliseDomainKey((string) $value);
            if (isset($projectsByKey[$domainKey])) {
                return $projectsByKey[$domainKey];
            }

            $projectKey = $this->normaliseProjectKey((string) $value);
            if (isset($projectsByKey[$projectKey])) {
                return $projectsByKey[$projectKey];
            }
        }

        return null;
    }

    private function normaliseDomainKey(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('#^https?://#', '', $value);
        $value = preg_replace('#^www\.#', '', $value);
        $value = explode('/', $value)[0] ?? $value;

        return trim($value);
    }

    private function normaliseProjectKey(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('#^https?://#', '', $value);
        $value = preg_replace('#^www\.#', '', $value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);

        return trim($value, '-');
    }
}
