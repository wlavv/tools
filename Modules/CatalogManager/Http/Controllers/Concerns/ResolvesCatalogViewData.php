<?php

namespace Modules\CatalogManager\Http\Controllers\Concerns;

trait ResolvesCatalogViewData
{
    protected function pageTitle(string $routeName): string
    {
        return config("catalogmanager.page_titles.$routeName", 'Catalog Manager');
    }
}
