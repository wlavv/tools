<?php

namespace Modules\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

abstract class BaseNotificationController extends Controller
{
    protected string $moduleConfigKey = 'notifications';
    protected string $moduleRoutePrefix = 'notifications';
    protected string $moduleTitle = 'Notifications';
    protected string $moduleIcon = 'fa-solid fa-bell';

    public function __construct()
    {
        parent::__construct();
    }

    protected function viewData(array $data = []): array
    {
        $fallback = $this->moduleHeaderData($data);

        $merged = array_merge($fallback, $data, [
            'pageTitle'       => $this->pageTitle ?: $fallback['pageTitle'],
            'pageTitleSuffix' => $this->pageTitleSuffix,
            'breadcrumbs'     => !empty($this->breadcrumbs) ? $this->breadcrumbs : $fallback['breadcrumbs'],
            'showBreadcrumbs' => $this->showBreadcrumbs,
            'actions'         => !empty($this->actions) ? $this->actions : $fallback['actions'],
            'accessList'      => $this->accessList,
        ]);

        foreach (['actions', 'pageActions', 'headerActions', 'actionList'] as $key) {
            if (empty($merged[$key])) {
                $merged[$key] = $merged['actions'] ?: $fallback['actions'];
            }
        }

        foreach (['breadcrumbs', 'breadCrumbs', 'breadcrumbItems'] as $key) {
            if (empty($merged[$key])) {
                $merged[$key] = $merged['breadcrumbs'] ?: $fallback['breadcrumbs'];
            }
        }

        return $merged;
    }

    protected function moduleHeaderData(array $data = []): array
    {
        $actions = $this->fallbackActions($data);
        $breadcrumbs = $this->fallbackBreadcrumbs();

        return [
            'pageTitle' => $this->fallbackPageTitle(),
            'title' => $this->fallbackPageTitle(),
            'pageSubtitle' => $this->fallbackPageSubtitle(),
            'subtitle' => $this->fallbackPageSubtitle(),
            'pageIcon' => $this->fallbackPageIcon(),
            'icon' => $this->fallbackPageIcon(),
            'breadcrumbs' => $breadcrumbs,
            'breadCrumbs' => $breadcrumbs,
            'breadcrumbItems' => $breadcrumbs,
            'actions' => $actions,
            'pageActions' => $actions,
            'headerActions' => $actions,
            'actionList' => $actions,
        ];
    }

    protected function fallbackPageTitle(): string
    {
        $currentRoute = Route::currentRouteName();
        $config = config($this->moduleConfigKey . '.page_titles.' . $currentRoute, []);

        return $config['title'] ?? $this->moduleTitle;
    }

    protected function fallbackPageSubtitle(): ?string
    {
        $currentRoute = Route::currentRouteName();
        $config = config($this->moduleConfigKey . '.page_titles.' . $currentRoute, []);

        return $config['subtitle'] ?? null;
    }

    protected function fallbackPageIcon(): string
    {
        $currentRoute = Route::currentRouteName();
        $config = config($this->moduleConfigKey . '.page_titles.' . $currentRoute, []);

        return $config['icon'] ?? $this->moduleIcon;
    }

    protected function fallbackBreadcrumbs(): array
    {
        $currentRoute = Route::currentRouteName();
        $config = config($this->moduleConfigKey . '.breadcrumbs', []);

        if (!$currentRoute || !isset($config[$currentRoute])) {
            return [];
        }

        $breadcrumbs = [];
        $visited = [];
        $route = $currentRoute;

        while ($route && isset($config[$route]) && !in_array($route, $visited, true)) {
            $visited[] = $route;
            $item = $config[$route];

            if (isset($item[0]) && is_array($item[0])) {
                return collect($item)->map(fn (array $crumb) => $this->normalizeBreadcrumbItem($crumb))->values()->all();
            }

            $breadcrumbs[] = $this->normalizeBreadcrumbItem([
                'label' => $item['label'] ?? $route,
                'route' => $route,
                'params' => $item['params'] ?? [],
                'translate' => $item['translate'] ?? true,
            ]);

            $route = $item['parent'] ?? null;
        }

        return array_reverse($breadcrumbs);
    }

    protected function normalizeBreadcrumbItem(array $item): array
    {
        $url = $item['url'] ?? null;
        $params = $item['params'] ?? [];

        if (!$url && !empty($item['route']) && Route::has($item['route'])) {
            $url = route($item['route'], $params);
        }

        $label = $item['label'] ?? $item['name'] ?? '';

        if (($item['translate'] ?? true) && is_string($label)) {
            $translated = __($label);
            $label = $translated !== $label ? $translated : $label;
        }

        return [
            'name' => $item['name'] ?? $label,
            'label' => $label,
            'url' => $url,
            'href' => $url,
            'active' => (bool) ($item['active'] ?? false),
            'icon' => $item['icon'] ?? null,
            'params' => $params,
            'translate' => $item['translate'] ?? true,
        ];
    }

    protected function fallbackActions(array $viewData = []): array
    {
        $currentRoute = Route::currentRouteName();
        $items = config($this->moduleConfigKey . '.actions.routes.' . $currentRoute, []);

        return collect($items)->map(function ($item, string $key) use ($viewData) {
            if ($item === false || $item === null || $item === true || !is_array($item)) {
                return null;
            }

            $url = $item['url'] ?? null;
            $routeParams = [];

            foreach (($item['route_params_from'] ?? []) as $paramKey) {
                if (array_key_exists($paramKey, $viewData)) {
                    $routeParams[] = $viewData[$paramKey];
                }
            }

            if (!$url && !empty($item['route']) && Route::has($item['route'])) {
                $url = route($item['route'], $routeParams);
            }

            return array_merge($item, [
                'key' => $item['key'] ?? $key,
                'name' => $item['name'] ?? $item['label'] ?? ucfirst(str_replace('_', ' ', $key)),
                'label' => $item['label'] ?? $item['name'] ?? ucfirst(str_replace('_', ' ', $key)),
                'url' => $url,
                'href' => $url,
                'class' => $item['class'] ?? 'lsg-action-btn lsg-action-btn--primary',
                'icon' => $item['icon'] ?? 'fa-solid fa-cog',
                'method' => strtoupper($item['method'] ?? 'GET'),
                'type' => $item['type'] ?? 'link',
            ]);
        })->filter(fn ($item) => is_array($item) && (!empty($item['url']) || (($item['type'] ?? null) === 'submit')))->values()->all();
    }
}
