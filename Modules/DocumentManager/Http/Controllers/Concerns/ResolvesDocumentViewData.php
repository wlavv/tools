<?php

namespace Modules\DocumentManager\Http\Controllers\Concerns;

trait ResolvesDocumentViewData
{
    protected function pageTitle(string $routeName): string
    {
        return config("documentmanager.page_titles.$routeName", 'Document Manager');
    }
}
