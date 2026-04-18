<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Config;
use App\Support\Breadcrumbs\BreadcrumbRegistry;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $defaultLang;

    protected array $breadcrumbs = [];
    protected array $actions = [];
    protected ?string $pageTitle = null;
    protected array $accessList = [];

    public function __construct()
    {
        $this->middleware('auth');

        $this->defaultLang = 1;
        Config::set('defaultLang', $this->defaultLang);

        $this->breadcrumbs = $this->resolveBreadcrumbs();
    }

    protected function setPageTitle(?string $title): void
    {
        $this->pageTitle = $title;
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
            'pageTitle'   => $this->pageTitle,
            'breadcrumbs' => $this->breadcrumbs,
            'actions'     => $this->actions,
            'accessList'  => $this->accessList,
        ], $data));
    }
}