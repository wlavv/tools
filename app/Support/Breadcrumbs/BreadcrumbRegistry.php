<?php

namespace App\Support\Breadcrumbs;

class BreadcrumbRegistry
{
    protected array $items = [];

    public function __construct()
    {
        $this->load();
    }

    protected function load(): void
    {
        $this->loadGlobalBreadcrumbs();
        $this->loadModuleBreadcrumbs();
    }

    protected function loadGlobalBreadcrumbs(): void
    {
        $file = config_path('breadcrumbs.php');

        if (!file_exists($file)) {
            return;
        }

        $config = include $file;

        if (is_array($config)) {
            $this->items = array_merge($this->items, $config);
        }
    }

    protected function loadModuleBreadcrumbs(): void
    {
        $modulesPath = base_path('Modules');

        foreach (glob($modulesPath . '/*/Config/breadcrumbs.php') as $file) {
            $config = include $file;

            if (is_array($config)) {
                $this->items = array_merge($this->items, $config);
            }
        }
    }

    public function getAll(): array
    {
        return $this->items;
    }
}