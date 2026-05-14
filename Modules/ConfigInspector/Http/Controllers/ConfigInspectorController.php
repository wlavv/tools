<?php

namespace Modules\ConfigInspector\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\ConfigInspector\Services\ConfigInspectorService;

class ConfigInspectorController extends Controller
{
    public function index(Request $request, ConfigInspectorService $service)
    {
        $active = $request->query('tab');
        $payload = $service->run($active);

        return $this->view('config-inspector::index', [
            'active' => $payload['active'],
            'inspectors' => $payload['inspectors'],
            'results' => $payload['results'],
            'global' => $payload['global'],
            'pageTitle' => __('config-inspector::page_titles.config_inspector.index'),
        ]);
    }
}
