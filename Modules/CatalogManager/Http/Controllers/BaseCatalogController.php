<?php

namespace Modules\CatalogManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

abstract class BaseCatalogController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->setModuleHomeRoute('catalog-manager.dashboard');

        $routeName = request()->route()?->getName();

        if ($routeName) {
            $this->setPageTitle(config("catalogmanager.page_titles.$routeName", $this->pageTitle));
            $this->setActions($this->catalogActionsFor($routeName));
        }
    }

    private function catalogActionsFor(string $routeName): array
    {
        $actions = config("catalogmanager.actions.$routeName", []);

        if (!is_array($actions)) {
            return [];
        }

        return array_values(array_filter(array_map(function (array $action, $index) {
            $route = $action['route'] ?? null;

            if (!$route || !Route::has($route)) {
                return null;
            }

            return [
                'key' => $action['key'] ?? 'catalog_action_' . $index,
                'label' => $action['label'] ?? $action['name'] ?? 'Action',
                'name' => $action['name'] ?? $action['label'] ?? 'Action',
                'icon' => $action['icon'] ?? 'fa-solid fa-circle',
                'class' => 'lsg-action-btn lsg-action-btn--' . $this->catalogActionTone($action['class'] ?? ''),
                'url' => route($route),
                'route' => $route,
                'type' => 'link',
            ];
        }, $actions, array_keys($actions))));
    }

    private function catalogActionTone(string $class): string
    {
        $class = strtolower($class);

        return match (true) {
            str_contains($class, 'success') => 'success',
            str_contains($class, 'warning') => 'warning',
            str_contains($class, 'danger') => 'danger',
            str_contains($class, 'primary') => 'gold',
            default => 'neutral',
        };
    }
}
