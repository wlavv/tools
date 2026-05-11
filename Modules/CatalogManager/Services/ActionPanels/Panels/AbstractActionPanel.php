<?php

namespace Modules\CatalogManager\Services\ActionPanels\Panels;

abstract class AbstractActionPanel
{
    protected function limit(): int
    {
        return (int) config('catalogmanager.action_panels.limit_per_panel', 8);
    }

    protected function productUrl(?int $id): ?string
    {
        try {
            return $id ? route('catalog-manager.products.show', $id, false) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function syncUrl(array $params = []): ?string
    {
        try {
            return route('catalog-manager.sync.index', $params, false);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
