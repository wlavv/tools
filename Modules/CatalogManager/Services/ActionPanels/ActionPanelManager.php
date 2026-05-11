<?php

namespace Modules\CatalogManager\Services\ActionPanels;

use Modules\CatalogManager\Services\ActionPanels\Contracts\ActionPanelInterface;
use Modules\CatalogManager\Support\CatalogLogger;

class ActionPanelManager
{
    public function resolve(array $context = []): array
    {
        $panels = config('catalogmanager.action_panels.panels', []);
        $results = [];

        foreach ($panels as $key => $panelConfig) {
            if (!($panelConfig['enabled'] ?? false)) {
                continue;
            }

            try {
                $provider = app($panelConfig['provider']);

                if (!$provider instanceof ActionPanelInterface) {
                    continue;
                }

                $result = $provider->resolve(array_merge($context, ['panel_key' => $key, 'panel_config' => $panelConfig]));
                $result->key = $key;
                $result->title = $panelConfig['title'] ?? $result->title;
                $result->description = $panelConfig['description'] ?? $result->description;
                $result->icon = $panelConfig['icon'] ?? $result->icon;
                $result->tone = $panelConfig['tone'] ?? $result->tone;

                $results[] = ['order' => $panelConfig['order'] ?? 999, 'panel' => $result->toArray()];
            } catch (\Throwable $e) {
                CatalogLogger::exception($e, ['panel' => $key, 'type' => 'action_panel']);
            }
        }

        usort($results, fn ($a, $b) => $a['order'] <=> $b['order']);

        return array_map(fn ($row) => $row['panel'], $results);
    }
}
