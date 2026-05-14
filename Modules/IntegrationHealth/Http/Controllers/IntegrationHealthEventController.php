<?php

namespace Modules\IntegrationHealth\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\IntegrationHealth\Models\IntegrationHealthEvent;
use Modules\IntegrationHealth\Services\IntegrationHealthService;

class IntegrationHealthEventController extends Controller
{
    public function index(Request $request)
    {
        $events = IntegrationHealthEvent::query()
            ->with('service')
            ->when($request->filled('severity'), fn ($q) => $q->where('severity', $request->severity))
            ->when($request->filled('status') && $request->status === 'open', fn ($q) => $q->whereNull('resolved_at'))
            ->when($request->filled('status') && $request->status === 'resolved', fn ($q) => $q->whereNotNull('resolved_at'))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return $this->view('integration-health::events.index', compact('events'));
    }

    public function resolve(IntegrationHealthEvent $event, IntegrationHealthService $healthService)
    {
        $healthService->resolveEvent($event, auth()->id());
        return back()->with('success', 'Event resolved.');
    }
}
