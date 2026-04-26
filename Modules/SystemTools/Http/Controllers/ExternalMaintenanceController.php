<?php

namespace Modules\SystemTools\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\SystemTools\Services\MaintenanceActionService;

class ExternalMaintenanceController extends Controller
{
    public function run(Request $request, string $action, MaintenanceActionService $service)
    {
        if (!config('system-tools.external_enabled')) {
            abort(403, 'External access disabled.');
        }

        $expectedToken = (string) config('system-tools.external_token');
        $token = (string) ($request->header('X-System-Token') ?: $request->get('token'));

        if ($expectedToken === '' || !hash_equals($expectedToken, $token)) {
            abort(403, 'Invalid token.');
        }

        return response()->json($service->run($action, true));
    }

    public function links(Request $request, MaintenanceActionService $service)
    {
        if (!config('system-tools.external_enabled')) {
            abort(403, 'External access disabled.');
        }

        $expectedToken = (string) config('system-tools.external_token');
        $token = (string) ($request->header('X-System-Token') ?: $request->get('token'));

        if ($expectedToken === '' || !hash_equals($expectedToken, $token)) {
            abort(403, 'Invalid token.');
        }

        $tools = collect($service->all())
            ->filter(fn ($tool) => !empty($tool['external']))
            ->map(function ($tool, $key) use ($token) {
                return [
                    'key' => $key,
                    'label' => $tool['label'] ?? $key,
                    'description' => $tool['description'] ?? null,
                    'risk' => $tool['risk'] ?? 'safe',
                    'method' => 'GET',
                    'url' => route('system-tools.external.run', [
                        'action' => $key,
                        'token' => $token,
                    ]),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'count' => $tools->count(),
            'links' => $tools,
        ]);
    }
}