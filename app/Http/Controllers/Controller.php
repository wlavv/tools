<?php

namespace App\Http\Controllers;

use App\Support\Actions\ActionResolver;
use App\Support\Breadcrumbs\BreadcrumbRegistry;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Modules\PermissionRoleManager\Services\RoutePermissionAccessService;

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

        $this->pageTitle       = $this->resolvePageTitle();
        $this->breadcrumbs     = $this->resolveBreadcrumbs();
        $this->showBreadcrumbs = !$this->isDashboardLikeRoute();
        $this->actions         = $this->resolveActions();

        $this->shareLayoutData();
    }

    protected function shareLayoutData(): void
    {
        View::share([
            'pageTitle'       => $this->pageTitle,
            'pageTitleSuffix' => $this->pageTitleSuffix,
            'breadcrumbs'     => $this->breadcrumbs,
            'showBreadcrumbs' => $this->showBreadcrumbs,
            'actions'         => $this->actions,
            'accessList'      => $this->accessList,
        ]);
    }

    protected function setPageTitle(?string $title): void
    {
        $this->pageTitle = $title;
        $this->shareLayoutData();
    }

    protected function setPageTitleSuffix(?string $suffix): void
    {
        $this->pageTitleSuffix = $suffix;
        $this->shareLayoutData();
    }

    protected function hideBreadcrumbs(): void
    {
        $this->showBreadcrumbs = false;
        $this->shareLayoutData();
    }

    protected function showBreadcrumbs(): void
    {
        $this->showBreadcrumbs = true;
        $this->shareLayoutData();
    }

    protected function setBreadcrumbs(array $items = []): void
    {
        $this->breadcrumbs = $items;
        $this->shareLayoutData();
    }

    protected function addBreadcrumb(string $label, ?string $url = null, array $params = [], bool $translate = true): void
    {
        $this->breadcrumbs[] = [
            'label'     => $label,
            'url'       => $url,
            'params'    => $params,
            'translate' => $translate,
        ];

        $this->shareLayoutData();
    }

    protected function setActions(?array $actions = null): void
    {
        $this->actions = $actions ?? [];
        $this->shareLayoutData();
    }

    protected function addAction(array $action): void
    {
        $this->customActions[] = $action;
        $this->actions = $this->resolveActions();
        $this->shareLayoutData();
    }

    protected function replaceAction(string $key, array $action): void
    {
        $this->disableDefaultAction($key);

        $action['key'] = $action['key'] ?? $key;
        $this->customActions[] = $action;
        $this->actions = $this->resolveActions();

        $this->shareLayoutData();
    }

    protected function disableDefaultAction(string $key): void
    {
        if (!in_array($key, $this->disabledDefaultActions, true)) {
            $this->disabledDefaultActions[] = $key;
        }

        $this->actions = $this->resolveActions();
        $this->shareLayoutData();
    }

    protected function enableOnlyActions(array $keys): void
    {
        $this->onlyActionKeys = array_values($keys);
        $this->actions = $this->resolveActions();
        $this->shareLayoutData();
    }

    protected function clearActions(): void
    {
        $this->customActions = [];
        $this->disabledDefaultActions = [];
        $this->onlyActionKeys = [];
        $this->actions = [];

        $this->shareLayoutData();
    }

    protected function setModuleHomeRoute(?string $routeName): void
    {
        $this->moduleHomeRoute = $routeName;
        $this->actions = $this->resolveActions();

        $this->shareLayoutData();
    }

    protected function setAccessList(?array $accessList = null): void
    {
        $this->accessList = $accessList ?? [];
        $this->shareLayoutData();
    }

    protected function addAccess(string $url, string $name, ?string $icon = null, ?string $image = null): void
    {
        $this->accessList[] = [
            'url'   => $url,
            'name'  => $name,
            'icon'  => $icon,
            'image' => $image,
        ];

        $this->shareLayoutData();
    }

    protected function addRouteAccess(string $routeName, string $name, ?string $icon = null, ?string $image = null, array $params = []): void
    {
        if (!Route::has($routeName)) {
            return;
        }

        $allowed = app(RoutePermissionAccessService::class)
            ->canAccessRouteName(auth()->id(), $routeName);

        if (!$allowed) {
            return;
        }

        $this->addAccess(route($routeName, $params), $name, $icon, $image);
    }

    protected function resetAccessList(): void
    {
        $this->accessList = [];
        $this->shareLayoutData();
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
            ],
        ]);
    }

    protected function resolveBreadcrumbs(): array
    {
        $route = request()->route();

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

            $rawLabel = $item['label'] ?? $current;
            $translate = $item['translate'] ?? true;

            $breadcrumbs[] = [
                'label'     => $this->resolveBreadcrumbLabel($rawLabel, $translate),
                'url'       => $this->safeRouteWhenAllowed($current, $item['params'] ?? []),
                'params'    => $item['params'] ?? [],
                'translate' => false,
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
        if (!$this->hasPageActions) {
            return [];
        }

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

    protected function safeRouteWhenAllowed(string $routeName, array $params = []): ?string
    {
        $allowed = app(RoutePermissionAccessService::class)
            ->canAccessRouteName(auth()->id(), $routeName);

        if (!$allowed) {
            return null;
        }

        return $this->safeRoute($routeName, $params);
    }

    protected function view(string $view, array $data = [])
    {
        $this->shareLayoutData();

        return View::make($view)->with(array_merge([
            'pageTitle'       => $this->pageTitle,
            'pageTitleSuffix' => $this->pageTitleSuffix,
            'breadcrumbs'     => $this->breadcrumbs,
            'showBreadcrumbs' => $this->showBreadcrumbs,
            'actions'         => $this->actions,
            'accessList'      => $this->accessList,
        ], $data));
    }

    protected function resolveBreadcrumbLabel(string $label, bool $translate = true): string
    {
        if (!$translate) {
            return $label;
        }

        if (str_contains($label, '::')) {
            $translated = __($label);
            return is_string($translated) && $translated !== $label ? $translated : $label;
        }

        if (str_starts_with($label, 'breadcrumbs.')) {
            $translated = __($label);
            return is_string($translated) && $translated !== $label ? $translated : $label;
        }

        $globalKey = 'breadcrumbs.' . $label;
        $translated = __($globalKey);

        if (is_string($translated) && $translated !== $globalKey) {
            return $translated;
        }

        $translated = __($label);

        return is_string($translated) && $translated !== $label ? $translated : $label;
    }
}
