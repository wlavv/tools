<?php

namespace App\Support\Breadcrumbs;

class BreadcrumbRegistry
{
    public function getAll(): array
    {
        $breadcrumbs = config('breadcrumbs', []);

        foreach ($this->getModuleBreadcrumbFiles() as $file) {
            $moduleBreadcrumbs = require $file;

            if (is_array($moduleBreadcrumbs)) {
                $breadcrumbs = array_merge($breadcrumbs, $moduleBreadcrumbs);
            }
        }

        return $breadcrumbs;
    }

    protected function getModuleBreadcrumbFiles(): array
    {
        $files = [];
        $modulesPath = base_path('Modules');

        if (!is_dir($modulesPath)) {
            return $files;
        }

        $paths = glob($modulesPath . '/*/Config/breadcrumbs.php');

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
}