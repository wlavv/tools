<?php

namespace App\Support\Actions;

class ActionResolver
{
    public function __construct(protected ActionRegistry $registry)
    {
    }

    public function resolve(
        array $disabledKeys = [],
        ?array $onlyKeys = null,
        array $customActions = [],
        ?string $moduleHomeRouteOverride = null,
    ): array {
        $route = request()->route();

        if (!$route) {
            return $this->normalizeCustomActions($customActions);
        }

        $routeName = $route->getName();

        if (!$routeName) {
            return $this->normalizeCustomActions($customActions);
        }

        $config = $this->registry->getAll();
        $routeConfig = $config['routes'][$routeName] ?? [];
        $routeAction = $this->resolveRouteAction($routeName);
        $modulePrefix = $this->getModulePrefix($routeName);

        $moduleHomeRoute = $moduleHomeRouteOverride
            ?: ($routeConfig['module_home_route'] ?? null)
            ?: ($config['module_home_routes'][$modulePrefix] ?? null)
            ?: ($modulePrefix ? $modulePrefix . '.index' : null);

        $definitions = $this->buildDefaultDefinitions(
            routeName: $routeName,
            routeAction: $routeAction,
            modulePrefix: $modulePrefix,
            moduleHomeRoute: $moduleHomeRoute,
            routeConfig: $routeConfig,
            routeParameters: $route->parameters(),
        );

        $definitions = $this->applyRouteConfigToDefinitions($definitions, $routeConfig, $route->parameters());
        $definitions = $this->removeDisabled($definitions, $disabledKeys);
        $definitions = $this->mergeCustomActions($definitions, $customActions);
        $definitions = $this->filterOnly($definitions, $onlyKeys);

        return array_values($definitions);
    }

    protected function buildDefaultDefinitions(
        string $routeName,
        string $routeAction,
        string $modulePrefix,
        ?string $moduleHomeRoute,
        array $routeConfig,
        array $routeParameters,
    ): array {
        $defaults = [];
        $createRoute = $routeConfig['create_route'] ?? ($modulePrefix ? $modulePrefix . '.create' : null);
        $editRoute = $routeConfig['edit_route'] ?? ($modulePrefix ? $modulePrefix . '.edit' : null);
        $showRoute = $routeConfig['show_route'] ?? ($modulePrefix ? $modulePrefix . '.show' : null);
        $deleteRoute = $routeConfig['delete_route'] ?? $routeName;

        switch ($routeAction) {
            case 'index':
                $defaults['new'] = $this->makeNewAction($createRoute);
                break;

            case 'create':
                $defaults['back'] = $this->makeBackAction($moduleHomeRoute);
                $defaults['save'] = $this->makeSaveAction();
                break;

            case 'show':
                $defaults['back'] = $this->makeBackAction($moduleHomeRoute);
                $defaults['edit'] = $this->makeEditAction($editRoute, $routeParameters);
                $defaults['delete'] = $this->makeDeleteAction($deleteRoute, $routeParameters);
                break;

            case 'edit':
                $defaults['back'] = $this->makeBackAction($moduleHomeRoute);
                $defaults['new'] = $this->makeNewAction($createRoute);
                $defaults['save'] = $this->makeSaveAction();
                $defaults['show'] = $this->makeShowAction($showRoute, $routeParameters);
                $defaults['delete'] = $this->makeDeleteAction($deleteRoute, $routeParameters);
                break;
        }

        return array_filter($defaults);
    }

    protected function applyRouteConfigToDefinitions(array $definitions, array $routeConfig, array $routeParameters): array
    {
        $reserved = ['module_home_route', 'create_route', 'edit_route', 'show_route', 'delete_route'];

        foreach ($routeConfig as $key => $value) {
            if (in_array($key, $reserved, true)) {
                continue;
            }

            if ($value === false || $value === null) {
                unset($definitions[$key]);
                continue;
            }

            if ($value === true) {
                continue;
            }

            if (is_string($value)) {
                $baseAction = $definitions[$key] ?? $this->makeActionByKey($key);
                if ($baseAction) {
                    $definitions[$key] = $this->applyRouteToAction($baseAction, $value, $routeParameters);
                }
                continue;
            }

            if (is_array($value)) {
                $action = $definitions[$key] ?? $this->makeActionByKey($key);

                if (!$action) {
                    continue;
                }

                if (isset($value['route'])) {
                    $action = $this->applyRouteToAction($action, $value['route'], $routeParameters);
                }

                $definitions[$key] = array_merge($action, $value, ['key' => $value['key'] ?? $key]);
            }
        }

        return $definitions;
    }

    protected function applyRouteToAction(array $action, string $routeName, array $routeParameters): array
    {
        $action['route'] = $routeName;
        $action['url'] = $this->safeRoute($routeName, $routeParameters);

        return $action;
    }

    protected function makeActionByKey(string $key): ?array
    {
        return match ($key) {
            'new'       => $this->makeNewAction(null),
            'edit'      => $this->makeEditAction(null, []),
            'show'      => $this->makeShowAction(null, []),
            'delete'    => $this->makeDeleteAction(null, []),
            'save'      => $this->makeSaveAction(),
            'back'      => $this->makeBackAction(null),
            default     => [
                'key' => $key,
                'name' => ucfirst(str_replace('_', ' ', $key)),
                'label' => ucfirst(str_replace('_', ' ', $key)),
                'icon' => 'fa-solid fa-cog',
                'class' => 'lsg-action-btn lsg-action-btn--neutral',
                'url' => null,
                'type' => 'link',
            ],
        };
    }

    protected function makeShowAction(?string $routeName, array $routeParameters = []): ?array
    {
        if (!$routeName) {
            return null;
        }

        return [
            'key' => 'show',
            'name' => 'Show',
            'label' => 'Show',
            'icon' => 'fa-solid fa-eye',
            'class' => 'lsg-action-btn lsg-action-btn--neutral',
            'route' => $routeName,
            'url' => $this->safeRoute($routeName, $routeParameters),
            'type' => 'link',
        ];
    }

    protected function makeBackAction(?string $routeName): ?array
    {
        if (!$routeName) {
            return null;
        }

        return [
            'key' => 'back',
            'name' => 'Back',
            'label' => 'Back',
            'icon' => 'fa-solid fa-angle-left',
            'class' => 'lsg-action-btn lsg-action-btn--back',
            'route' => $routeName,
            'url' => $this->safeRoute($routeName),
            'type' => 'link',
        ];
    }

    protected function makeNewAction(?string $routeName): ?array
    {
        if (!$routeName) {
            return null;
        }

        return [
            'key' => 'new',
            'name' => 'New',
            'label' => 'New',
            'icon' => 'fa-solid fa-plus',
            'class' => 'lsg-action-btn lsg-action-btn--success',
            'route' => $routeName,
            'url' => $this->safeRoute($routeName),
            'type' => 'link',
        ];
    }

    protected function makeEditAction(?string $routeName, array $routeParameters): ?array
    {
        if (!$routeName) {
            return null;
        }

        return [
            'key' => 'edit',
            'name' => 'Edit',
            'label' => 'Edit',
            'icon' => 'fa-solid fa-pencil',
            'class' => 'lsg-action-btn lsg-action-btn--warning',
            'route' => $routeName,
            'url' => $this->safeRoute($routeName, $routeParameters),
            'type' => 'link',
        ];
    }

    protected function makeDeleteAction(?string $routeName, array $routeParameters): ?array
    {
        if (!$routeName) {
            return null;
        }

        return [
            'key' => 'delete',
            'name' => 'Delete',
            'label' => 'Delete',
            'icon' => 'fa-solid fa-trash',
            'class' => 'lsg-action-btn lsg-action-btn--danger',
            'route' => $routeName,
            'url' => $this->safeRoute($routeName, $routeParameters),
            'type' => 'delete',
            'method' => 'DELETE',
            'confirm' => true,
        ];
    }

    protected function makeSaveAction(): array
    {
        return [
            'key' => 'save',
            'name' => 'Save',
            'label' => 'Save',
            'icon' => 'fa-solid fa-floppy-disk',
            'class' => 'lsg-action-btn lsg-action-btn--gold',
            'url' => null,
            'type' => 'submit',
        ];
    }

    protected function removeDisabled(array $definitions, array $disabledKeys): array
    {
        foreach ($disabledKeys as $key) {
            unset($definitions[$key]);
        }

        return $definitions;
    }

    protected function mergeCustomActions(array $definitions, array $customActions): array
    {
        foreach ($this->normalizeCustomActions($customActions) as $action) {
            $definitions[$action['key']] = $action;
        }

        return $definitions;
    }

    protected function filterOnly(array $definitions, ?array $onlyKeys): array
    {
        if ($onlyKeys === null) {
            return $definitions;
        }

        $filtered = [];

        foreach ($onlyKeys as $key) {
            if (isset($definitions[$key])) {
                $filtered[$key] = $definitions[$key];
            }
        }

        return $filtered;
    }

    protected function normalizeCustomActions(array $actions): array
    {
        $normalized = [];

        foreach ($actions as $action) {
            if (!is_array($action)) {
                continue;
            }

            $key = $action['key'] ?? null;

            if (!$key) {
                continue;
            }

            if (!isset($action['class'])) {
                $action['class'] = 'lsg-action-btn lsg-action-btn--neutral';
            }

            $normalized[$key] = $action;
        }

        return $normalized;
    }

    protected function getModulePrefix(string $routeName): string
    {
        return explode('.', $routeName)[0] ?? '';
    }

    protected function resolveRouteAction(string $routeName): string
    {
        $parts = explode('.', $routeName);
        return end($parts) ?: 'index';
    }

    protected function safeRoute(?string $routeName, array $params = []): ?string
    {
        if (!$routeName) {
            return null;
        }

        try {
            return route($routeName, $params);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
