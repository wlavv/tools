<?php

namespace Modules\SystemLogs\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\SystemLogs\Services\SystemLogsService;

class SystemLogsController extends Controller
{
    protected SystemLogsService $logs;

    public function __construct(SystemLogsService $logs)
    {
        $this->middleware('auth');
        $this->logs = $logs;

        if (method_exists($this, 'setIndexPage')) {
            $this->setIndexPage('system-logs', 'system_logs.index');
        }
    }

    public function index()
    {
        $logs = $this->logs->latest();

        $stats = [
            'total' => $logs->count(),
            'error' => $logs->where('level', 'error')->count(),
            'warning' => $logs->where('level', 'warning')->count(),
            'info' => $logs->where('level', 'info')->count(),
        ];

        return $this->view('system-logs::Index', [
            'logs' => $logs,
            'stats' => $stats,
            'pageTitle' => 'System Logs',
            'pageSubtitle' => 'Monitor recent platform events and create manual log entries when needed.',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'level' => ['required', 'string', 'max:50'],
            'message' => ['required', 'string'],
            'context' => ['nullable', 'string'],
        ]);

        $this->logs->create(
            strtolower(trim($validated['level'])),
            trim($validated['message']),
            $validated['context'] ?? null
        );

        return redirect()
            ->route('system_logs.index')
            ->with('success', 'Log created successfully.');
    }
}
