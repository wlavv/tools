<?php

namespace Modules\ConfigInspector\Inspectors;

class BreadcrumbActionInspector extends BaseInspector
{
    public function key(): string { return 'breadcrumbs_actions'; }
    public function label(): string { return 'Breadcrumbs & Actions'; }

    public function inspect(): array
    {
        $items = [];
        $modulesPath = base_path('Modules');
        $modules = is_dir($modulesPath) ? array_filter(glob($modulesPath . '/*'), 'is_dir') : [];

        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            foreach (['Config/breadcrumbs.php', 'Config/actions.php', 'Config/page_titles.php'] as $relative) {
                $path = $modulePath . '/' . $relative;
                $items[] = $this->item(file_exists($path) ? 'success' : 'warning', $moduleName . ' ' . $relative, file_exists($path) ? 'Configuração encontrada.' : 'Configuração em falta.', ['path' => $path]);
            }
        }

        return $items;
    }
}
