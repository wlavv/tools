<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\CatalogManager\Services\IssuePanels\IssuePanelManager;

class IssuePanelController extends BaseCatalogController
{
    public function index(IssuePanelManager $manager)
    {
        $panels = $manager->resolve();

        return view('catalogmanager::issue-panels.index', compact('panels'));
    }

    public function data(IssuePanelManager $manager): JsonResponse
    {
        return response()->json(['success' => true, 'panels' => $manager->resolve()]);
    }
}
