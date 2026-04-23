<?php

namespace App\Http\Controllers;

use App\Support\Actions\ActionResolver;
use App\Support\Breadcrumbs\BreadcrumbRegistry;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Config;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $defaultLang;

    protected array $breadcrumbs = [];
    protected array $actions = [];
    protected array $accessList = [];
    protected ?string $pageTitle = null;
    protected ?string $pageTitleSuffix = null;
    protected bool $showBreadcrumbs = true;

    protected array $customActions = [];
    protected array $disabledDefaultActions = [];
    protected ?array $onlyActionKeys = null;
    protected ?string $moduleHomeRoute = null;

    protected bool $hasPageActions = true;

    public function __construct()
    {
        $this->middleware('auth');

        $this->defaultLang = 1;
        Config::set('defaultLang', $this->defaultLang);

        $this->pageTitle = $this->resolvePageTitle();
        $this->breadcrumbs = $this->resolveBreadcrumbs();
        $this->showBreadcrumbs = !$this->isDashboardLikeRoute();
        $this->actions = $this->resolveActions();

    }

    protected function setPageTitle(?string $title): void
    {
        $this->pageTitle = $title;
    }

    protected function setPageTitleSuffix(?string $suffix): void
    {
        $this->pageTitleSuffix = $suffix;
    }

    protected function hideBreadcrumbs(): void
    {
        $this->showBreadcrumbs = false;
    }

    protected function showBreadcrumbs(): void
    {
        $this->showBreadcrumbs = true;
    }

    protected function setBreadcrumbs(array $items = []): void
    {
        $this->breadcrumbs = $items;
    }

    protected function addBreadcrumb(string $label, ?string $url = null, array $params = [], bool $translate = true): void
    {
        $this->breadcrumbs[] = [
            'label'     => $label,
            'url'       => $url,
            'params'    => $params,
            'translate' => $translate,
        ];
    }

    protected function setActions(?array $actions = null): void
    {
        $this->actions = $actions ?? [];
    }

    protected function addAction(array $action): void
    {
        $this->customActions[] = $action;
        $this->actions = $this->resolveActions();
    }

    protected function replaceAction(string $key, array $action): void
    {
        $this->disableDefaultAction($key);
        $action['key'] = $action['key'] ?? $key;
        $this->customActions[] = $action;
        $this->actions = $this->resolveActions();
    }

    protected function disableDefaultAction(string $key): void
    {
        if (!in_array($key, $this->disabledDefaultActions, true)) {
            $this->disabledDefaultActions[] = $key;
        }

        $this->actions = $this->resolveActions();
    }

    protected function enableOnlyActions(array $keys): void
    {
        $this->onlyActionKeys = array_values($keys);
        $this->actions = $this->resolveActions();
    }

    protected function clearActions(): void
    {
        $this->customActions = [];
        $this->disabledDefaultActions = [];
        $this->onlyActionKeys = [];
        $this->actions = [];
    }

    protected function setModuleHomeRoute(?string $routeName): void
    {
        $this->moduleHomeRoute = $routeName;
        $this->actions = $this->resolveActions();
    }

    protected function setAccessList(?array $accessList = null): void
    {
        $this->accessList = $accessList ?? [];
    }

    protected function addAccess(string $url, string $name, ?string $icon = null, ?string $image = null): void
    {
        $this->accessList[] = [
            'url'   => $url,
            'name'  => $name,
            'icon'  => $icon,
            'image' => $image,
        ];
    }

    protected function resetAccessList(): void
    {
        $this->accessList = [];
    }

    protected function setIndexPage(string $sectionKey, string $routeName): void
    {
        $this->setPageTitle($sectionKey);

        $this->setBreadcrumbs([
            [
                'label'     => $sectionKey,
                'url'       => $this->safeRoute($routeName),
                'params'    => [],
                'translate' => true,
            ]
        ]);
    }

    protected function resolveBreadcrumbs(): array
    {
        $route = request()->route();
        
        //system_log('info', 'Visited ' . $route->getName());

        if (!$route) {
            return [];
        }

        $routeName = $route->getName();

        if (!$routeName) {
            return [];
        }

        $config = app(BreadcrumbRegistry::class)->getAll();

        if (!isset($config[$routeName])) {
            return [];
        }

        $breadcrumbs = [];
        $visited = [];
        $current = $routeName;

        while ($current && isset($config[$current]) && !in_array($current, $visited, true)) {
            $visited[] = $current;

            $item = $config[$current];

            $breadcrumbs[] = [
                'label'     => $item['label'] ?? $current,
                'url'       => $this->safeRoute($current),
                'params'    => $item['params'] ?? [],
                'translate' => $item['translate'] ?? true,
            ];

            $current = $item['parent'] ?? null;
        }

        return array_reverse($breadcrumbs);
    }

    protected function resolvePageTitle(): ?string
    {
        $route = request()->route();

        if (!$route) {
            return null;
        }

        $routeName = $route->getName();

        if (!$routeName) {
            return null;
        }

        $globalKey = 'page_titles.' . $routeName;
        $globalTranslated = __($globalKey);

        if ($globalTranslated !== $globalKey) {
            return $globalTranslated;
        }

        $parts = explode('.', $routeName);
        $modulePrefix = $parts[0] ?? null;

        if ($modulePrefix) {
            $moduleNamespace = str_replace('_', '-', $modulePrefix);
            $moduleKey = $moduleNamespace . '::page_titles.' . $routeName;
            $moduleTranslated = __($moduleKey);

            if ($moduleTranslated !== $moduleKey) {
                return $moduleTranslated;
            }
        }

        return null;
    }

    protected function resolveActions(): array
    {
        if (!$this->hasPageActions) return [];

        return app(ActionResolver::class)->resolve(
            disabledKeys: $this->disabledDefaultActions,
            onlyKeys: $this->onlyActionKeys,
            customActions: $this->customActions,
            moduleHomeRouteOverride: $this->moduleHomeRoute,
        );
    }

    protected function isDashboardLikeRoute(): bool
    {
        return request()->routeIs('dashboard.index') || request()->routeIs('home.index');
    }

    protected function safeRoute(string $routeName, array $params = []): ?string
    {
        try {
            return route($routeName, $params);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function view(string $view, array $data = [])
    {
        return \View::make($view)->with(array_merge([
            'pageTitle'       => $this->pageTitle,
            'pageTitleSuffix' => $this->pageTitleSuffix,
            'breadcrumbs'     => $this->breadcrumbs,
            'showBreadcrumbs' => $this->showBreadcrumbs,
            'actions'         => $this->actions,
            'accessList'      => $this->accessList,
        ], $data));
    }
}
