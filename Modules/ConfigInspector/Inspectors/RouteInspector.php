<?php

namespace Modules\ConfigInspector\Inspectors;

use Illuminate\Support\Facades\Route;

class RouteInspector extends BaseInspector
{
    public function key(): string { return 'routes'; }
    public function label(): string { return 'Routes'; }

    public function inspect(): array
    {
        $items = [];
        $names = [];
        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if (!$name) {
                continue;
            }
            $names[$name] = ($names[$name] ?? 0) + 1;
        }

        foreach ($names as $name => $count) {
            if ($count > 1) {
                $items[] = $this->item('error', 'Duplicate route name', 'Nome de rota duplicado: ' . $name, ['name' => $name, 'count' => $count]);
            }
        }

        $configRoutes = array_keys(config('config-inspector.inspectors', []));
        $items[] = $this->item('info', 'Routes loaded', 'Total de rotas nomeadas: ' . count($names), ['named_routes' => count($names)]);

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if (!$name || !str_starts_with($name, 'config_inspector.')) {
                continue;
            }
            $items[] = $this->item('success', 'Config Inspector route', $name . ' → ' . $route->uri(), ['methods' => $route->methods(), 'action' => $route->getActionName()]);
        }

        return $items;
    }
}
