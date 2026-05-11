<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\CatalogManager\Services\ActionPanels\ActionPanelManager;

class ActionPanelController extends BaseCatalogController
{
    public function index(ActionPanelManager $manager)
    {
        $panels = $manager->resolve();

        return view('catalogmanager::action-panels.index', compact('panels'));
    }

    public function data(ActionPanelManager $manager): JsonResponse
    {
        return response()->json(['success' => true, 'panels' => $manager->resolve()]);
    }
}
