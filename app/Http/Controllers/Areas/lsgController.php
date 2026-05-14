<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\CatalogManager\Services\StorePageSpeedInsightsService;
use Modules\CatalogManager\Support\CatalogTable;
use Modules\PermissionRoleManager\Services\RoutePermissionAccessService;

class lsgController extends Controller
{
    protected bool $hasPageActions = false;

    public function index(StorePageSpeedInsightsService $pageSpeed)
    {
        $this->addRouteAccess('catalog-manager.stores.create', 'Novo site', 'fa-solid fa-plus');
        $this->addRouteAccess('catalog-manager.stores.index', 'Gerir sites', 'fa-solid fa-globe');
        $this->addRouteAccess('multiStore.index', 'MultiStore', 'fa-solid fa-store');
        $this->addRouteAccess('webcatalogue.index', 'Web Catalogue', 'fa-solid fa-book-open');
        $this->addRouteAccess('catalog-manager.dashboard', 'Catalog Manager', 'fa-solid fa-boxes-stacked');

        $sites = CatalogTable::exists('catalog_stores')
            ? DB::table('catalog_stores')
                ->orderByRaw("FIELD(site_kind, 'group', 'store', 'service', 'showcase', 'labs')")
                ->orderBy('name')
                ->get()
            : collect();

        $sites = $this->attachProjectUrls($sites);

        $groups = collect([
            'group' => ['label' => 'Site grupo', 'icon' => 'fa-solid fa-building', 'description' => 'Presenca institucional do grupo.'],
            'store' => ['label' => 'Sites lojas', 'icon' => 'fa-solid fa-store', 'description' => 'Lojas reais que entram no fluxo MultiStore.'],
            'service' => ['label' => 'Sites servicos', 'icon' => 'fa-solid fa-briefcase', 'description' => 'Servicos digitais ou operacionais.'],
            'showcase' => ['label' => 'Sites mostra', 'icon' => 'fa-solid fa-display', 'description' => 'Showcases, landing pages e presencas demonstrativas.'],
            'labs' => ['label' => 'Site labs', 'icon' => 'fa-solid fa-flask', 'description' => 'Experiencias, prototipos e ambientes de teste publicos.'],
        ])->map(function (array $meta, string $key) use ($sites) {
            $items = $sites->filter(fn ($site) => ($site->site_kind ?? 'store') === $key)->values();

            return array_merge($meta, [
                'key' => $key,
                'count' => $items->count(),
                'active' => $items->where('active', true)->count(),
                'sites' => $items,
            ]);
        });

        return $this->view('areas/lsg/index', [
            'sites' => $sites,
            'groups' => $groups,
            'pageSpeedMetrics' => $pageSpeed->todayMetricsForStores($sites),
            'pageSpeedMetricsByStrategy' => [
                'mobile' => $pageSpeed->todayMetricsForStores($sites, 'mobile'),
                'desktop' => $pageSpeed->todayMetricsForStores($sites, 'desktop'),
            ],
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

        $settings = json_decode((string) ($site->settings ?? ''), true);
        if (is_array($settings)) {
            foreach (['project_id', 'id_project'] as $key) {
                if (!empty($settings[$key]) && $projectsById->has((int) $settings[$key])) {
                    return $projectsById->get((int) $settings[$key]);
                }
            }
        }

        foreach ([$site->domain ?? null, $site->code ?? null, $site->name ?? null] as $value) {
            if (!$value) {
                continue;
            }

            $domainKey = $this->normaliseDomainKey($value);
            if (isset($projectsByKey[$domainKey])) {
                return $projectsByKey[$domainKey];
            }

            $projectKey = $this->normaliseProjectKey($value);
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
