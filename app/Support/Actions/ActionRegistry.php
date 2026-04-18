<?php

namespace App\Support\Actions;

class ActionRegistry
{
    public function getAll(): array
    {
        $actions = config('actions', []);

        foreach ($this->getModuleActionFiles() as $file) {
            $moduleActions = require $file;

            if (!is_array($moduleActions)) {
                continue;
            }

            $actions = $this->mergeModuleConfig($actions, $moduleActions);
        }

        return $actions;
    }

    protected function getModuleActionFiles(): array
    {
        $files = [];
        $modulesPath = base_path('Modules');

        if (!is_dir($modulesPath)) {
            return $files;
        }

        $paths = glob($modulesPath . '/*/Config/actions.php');

        if (!$paths) {
            return $files;
        }

        foreach ($paths as $file) {
            if (is_file($file)) {
                $files[] = $file;
            }
        }

        return $files;
    }

    protected function mergeModuleConfig(array $base, array $module): array
    {
        if (isset($module['routes']) && is_array($module['routes'])) {
            $base['routes'] = array_merge($base['routes'] ?? [], $module['routes']);
        }

        if (isset($module['module_home_routes']) && is_array($module['module_home_routes'])) {
            $base['module_home_routes'] = array_merge($base['module_home_routes'] ?? [], $module['module_home_routes']);
        }

        if (isset($module['defaults']) && is_array($module['defaults'])) {
            $base['defaults'] = array_merge($base['defaults'] ?? [], $module['defaults']);
        }

        return $base;
    }
}
