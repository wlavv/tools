<?php

namespace Modules\CatalogManager\Services\IssuePanels\Panels;

abstract class AbstractIssuePanel
{
    protected function limit(): int
    {
        return (int) config('catalogmanager.issue_panels.limit_per_panel', 8);
    }

    protected function productUrl(?int $id): ?string
    {
        try {
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
