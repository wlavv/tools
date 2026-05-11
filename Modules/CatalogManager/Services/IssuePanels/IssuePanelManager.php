<?php

namespace Modules\CatalogManager\Services\IssuePanels;

use Modules\CatalogManager\Services\IssuePanels\Contracts\IssuePanelInterface;
use Modules\CatalogManager\Support\CatalogLogger;

class IssuePanelManager
{
    public function resolve(array $context = []): array
    {
        $panels = config('catalogmanager.issue_panels.panels', []);
        $results = [];

        foreach ($panels as $key => $panelConfig) {
            if (!($panelConfig['enabled'] ?? false)) {
                continue;
            }

            try {
                $provider = app($panelConfig['provider']);

                if (!$provider instanceof IssuePanelInterface) {
                    continue;
                }

                $result = $provider->resolve($context);
                $result->key = $key;
                $result->title = $panelConfig['title'] ?? $result->title;
                $result->description = $panelConfig['description'] ?? $result->description;
                $result->icon = $panelConfig['icon'] ?? $result->icon;
                $result->tone = $panelConfig['tone'] ?? $result->tone;

                $results[] = ['order' => $panelConfig['order'] ?? 999, 'panel' => $result->toArray()];
            } catch (\Throwable $e) {
                CatalogLogger::exception($e, ['panel' => $key, 'type' => 'issue_panel']);
            }
        }

        usort($results, fn ($a, $b) => $a['order'] <=> $b['order']);

        return array_map(fn ($row) => $row['panel'], $results);
    }
}
