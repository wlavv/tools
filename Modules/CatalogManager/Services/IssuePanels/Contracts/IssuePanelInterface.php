<?php

namespace Modules\CatalogManager\Services\IssuePanels\Contracts;

use Modules\CatalogManager\Services\IssuePanels\IssuePanelResult;

interface IssuePanelInterface
{
    public function resolve(array $context = []): IssuePanelResult;
}
