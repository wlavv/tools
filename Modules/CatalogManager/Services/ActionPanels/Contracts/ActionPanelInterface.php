<?php

namespace Modules\CatalogManager\Services\ActionPanels\Contracts;

use Modules\CatalogManager\Services\ActionPanels\ActionPanelResult;

interface ActionPanelInterface
{
    public function resolve(array $context = []): ActionPanelResult;
}
