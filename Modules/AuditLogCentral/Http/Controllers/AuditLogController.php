<?php

namespace Modules\AuditLogCentral\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\AuditLogCentral\Models\AuditLog;

class AuditLogController extends Controller
{
    public function dashboard()
    {
        $totals = [
            'total' => AuditLog::count(),
            'today' => AuditLog::whereDate('occurred_at', today())->count(),
            'warnings' => AuditLog::whereIn('severity', ['warning', 'error', 'critical', 'security'])->count(),
            'security' => AuditLog::where('severity', 'security')->count(),
        ];

        $byModule = AuditLog::select('module', DB::raw('count(*) as total'))
            ->groupBy('module')->orderByDesc('total')->limit(10)->get();

        $recent = AuditLog::with(['tags'])->latest('occurred_at')->limit(12)->get();

        return $this->view('audit-log-central::dashboard', compact('totals', 'byModule', 'recent'));
    }

    public function index(Request $request)
    {
        $filters = $request->only(['module', 'severity', 'status', 'event', 'user', 'entity_type', 'entity_id', 'from', 'to']);

        $logs = AuditLog::with(['tags', 'changes'])
            ->filters($filters)
            ->latest('occurred_at')
            ->paginate(25)
            ->withQueryString();

        $modules = AuditLog::select('module')->distinct()->orderBy('module')->pluck('module');
        $severities = config('audit-log-central.severities', []);

        return $this->view('audit-log-central::index', compact('logs', 'filters', 'modules', 'severities'));
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load(['changes', 'tags', 'relations']);
        return $this->view('audit-log-central::show', compact('auditLog'));
    }

    public function entityTimeline(string $entityType, string $entityId)
    {
        $logs = AuditLog::with(['changes', 'tags', 'relations'])
            ->where('auditable_type', $entityType)
            ->where('auditable_id', $entityId)
            ->latest('occurred_at')
            ->paginate(50);

        return $this->view('audit-log-central::timeline', compact('logs', 'entityType', 'entityId'));
    }
}
