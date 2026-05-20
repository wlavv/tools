<?php

namespace Modules\ModuleComplianceCenter\Http\Controllers\Concerns;

trait PreparesComplianceCenterPage
{
    protected function prepareCompliancePage(string $title, array $trail = [], array $actions = []): void
    {
        $breadcrumbs = [
            [
                'label' => 'Dashboard',
                'url' => route('dashboard.index'),
                'translate' => false,
            ],
            [
                'label' => 'Compliance Center',
                'url' => route('module_compliance_center.dashboard'),
                'translate' => false,
            ],
        ];

        foreach ($trail as $item) {
            $breadcrumbs[] = array_merge([
                'url' => null,
                'translate' => false,
            ], is_array($item) ? $item : ['label' => (string) $item]);
        }

        $this->setPageTitle($title);
        $this->setBreadcrumbs($breadcrumbs);
        $this->setActions($actions);
    }

    protected function actionLink(string $key, string $label, string $icon, string $route, array $params = []): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'icon' => $icon,
            'url' => route($route, $params),
            'route' => $route,
            'type' => 'link',
        ];
    }
}
