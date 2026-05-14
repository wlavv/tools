<?php

namespace Modules\IntegrationHealth\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Modules\IntegrationHealth\Models\IntegrationHealthService as HealthService;

class IntegrationHealthIntegrationController extends Controller
{
    public function index()
    {
        $services = HealthService::query()->latest()->paginate(25);
        return $this->view('integration-health::integrations.index', compact('services'));
    }

    public function create()
    {
        return $this->view('integration-health::integrations.form', ['service' => new HealthService()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        HealthService::create($data);

        return redirect()->route('integration_health.integrations.index')->with('success', 'Integration created.');
    }

    public function edit(HealthService $integration)
    {
        return $this->view('integration-health::integrations.form', ['service' => $integration]);
    }

    public function update(Request $request, HealthService $integration)
    {
        $data = $this->validated($request, $integration->id);
        $integration->update($data);

        return redirect()->route('integration_health.integrations.index')->with('success', 'Integration updated.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'max:120', Rule::unique('integration_health_services', 'slug')->ignore($ignoreId)],
            'name' => ['required', 'string', 'max:180'],
            'type' => ['required', 'string', 'max:60'],
            'status' => ['required', Rule::in(config('integration-health.statuses'))],
            'health_score' => ['required', 'integer', 'min:0', 'max:100'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $data['is_enabled'] = $request->boolean('is_enabled');

        return $data;
    }
}
