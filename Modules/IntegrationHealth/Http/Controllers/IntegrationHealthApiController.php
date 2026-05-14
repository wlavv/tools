<?php

namespace Modules\IntegrationHealth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\IntegrationHealth\Services\IntegrationHealthService;

class IntegrationHealthApiController extends Controller
{
    public function heartbeat(Request $request, IntegrationHealthService $healthService)
    {
        $data = $request->validate([
            'service_slug' => ['required', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(config('integration-health.statuses'))],
            'response_time_ms' => ['nullable', 'integer', 'min:0'],
            'payload' => ['nullable', 'array'],
        ]);

        $service = $healthService->recordHeartbeat(
            $data['service_slug'],
            $data['payload'] ?? [],
            $data['response_time_ms'] ?? null,
            $data['status'] ?? 'online'
        );

        return response()->json(['ok' => true, 'service' => $service]);
    }

    public function event(Request $request, IntegrationHealthService $healthService)
    {
        $data = $request->validate([
            'service_slug' => ['required', 'string', 'max:120'],
            'severity' => ['required', Rule::in(config('integration-health.severities'))],
            'event_type' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:180'],
            'message' => ['nullable', 'string'],
            'payload' => ['nullable', 'array'],
        ]);

        $event = $healthService->recordEvent(
            $data['service_slug'],
            $data['severity'],
            $data['event_type'],
            $data['title'],
            $data['message'] ?? null,
            $data['payload'] ?? []
        );

        return response()->json(['ok' => true, 'event' => $event]);
    }

    public function metric(Request $request, IntegrationHealthService $healthService)
    {
        $data = $request->validate([
            'service_slug' => ['required', 'string', 'max:120'],
            'metric' => ['required', 'string', 'max:120'],
            'value' => ['required', 'numeric'],
            'unit' => ['nullable', 'string', 'max:30'],
            'payload' => ['nullable', 'array'],
        ]);

        $metric = $healthService->recordMetric(
            $data['service_slug'],
            $data['metric'],
            (float) $data['value'],
            $data['unit'] ?? null,
            $data['payload'] ?? []
        );

        return response()->json(['ok' => true, 'metric' => $metric]);
    }
}
