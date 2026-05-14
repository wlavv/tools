<?php

namespace Modules\ConfigInspector\Inspectors;

class OverviewInspector extends BaseInspector
{
    public function key(): string { return 'overview'; }
    public function label(): string { return 'Overview'; }

    public function inspect(): array
    {
        $items = [];
        $items[] = $this->item('success', 'Config Inspector online', 'Módulo carregado e operacional.', ['module' => 'ConfigInspector']);
        $items[] = $this->item('info', 'PHP', 'Versão PHP: ' . PHP_VERSION, ['php_version' => PHP_VERSION]);
        $items[] = $this->item('info', 'Laravel', 'Versão Laravel: ' . app()->version(), ['laravel_version' => app()->version()]);
        $items[] = $this->item('info', 'Base path', base_path(), ['base_path' => base_path()]);
        return $items;
    }
}
