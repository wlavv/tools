<?php

namespace Modules\SystemLogs\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Backoffice\AcknowledgementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\SystemLogs\Models\SystemLog;
use Modules\SystemLogs\Services\SystemLogsService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemLogsController extends Controller
{
    private const ACK_SOURCE = 'system_logs.log';
    private const ACTIONABLE_LEVELS = ['error', 'critical', 'alert', 'emergency'];

    protected SystemLogsService $logs;

    public function __construct(SystemLogsService $logs)
    {
        $this->logs = $logs;
        parent::__construct();
    }

    public function index(AcknowledgementService $acknowledgements)
    {
        $logs = $this->logs->latest();
        $actionableLogs = $logs
            ->filter(fn ($log) => in_array(strtolower((string) $log->level), self::ACTIONABLE_LEVELS, true))
            ->values();
        $acknowledgedLogIds = $acknowledgements->acknowledgedIds(self::ACK_SOURCE, $actionableLogs->pluck('id'));
        $unacknowledgedErrorLogs = $actionableLogs
            ->reject(fn ($log) => in_array((string) $log->id, $acknowledgedLogIds, true))
            ->take(10)
            ->values();

        $stats = [
            'total'   => $logs->count(),
            'error'   => $logs->where('level', 'error')->count(),
            'warning' => $logs->where('level', 'warning')->count(),
            'info'    => $logs->where('level', 'info')->count(),
            'success' => $logs->where('level', 'success')->count(),
            'unacknowledged' => $unacknowledgedErrorLogs->count(),
        ];

        return $this->view('system-logs::Index', [
            'logs' => $logs,
            'stats' => $stats,
            'acknowledgedLogIds' => $acknowledgedLogIds,
            'unacknowledgedErrorLogs' => $unacknowledgedErrorLogs,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'level' => ['required', 'string', 'max:50'],
            'message' => ['required', 'string'],
            'context' => ['nullable', 'string'],
        ]);

        $this->logs->create(
            strtolower(trim($validated['level'])),
            trim($validated['message']),
            $validated['context'] ?? null,
        );

        return redirect()
            ->route('system_logs.index')
            ->with('success', __('system-logs::messages.created_successfully'));
    }

    public function export(): StreamedResponse
    {
        $filename = 'system-logs-' . now()->format('Y-m-d-His') . '.csv';
        $logs = $this->logs->latest(5000);

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'level', 'message', 'context', 'user_id', 'created_at']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->level,
                    $log->message,
                    $log->context,
                    $log->user_id,
                    optional($log->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function acknowledge(SystemLog $log, AcknowledgementService $acknowledgements): RedirectResponse
    {
        $acknowledgements->acknowledge(self::ACK_SOURCE, $log->id, [
            'level' => $log->level,
            'message' => $log->message,
        ]);

        return redirect()
            ->route('system_logs.index')
            ->with('success', __('system-logs::messages.acknowledged_successfully'));
    }

    public function acknowledgeErrors(Request $request, AcknowledgementService $acknowledgements): RedirectResponse
    {
        $ids = collect($request->input('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($ids->isEmpty()) {
            return redirect()->route('system_logs.index');
        }

        SystemLog::query()
            ->whereIn('id', $ids)
            ->whereIn(DB::raw('LOWER(level)'), self::ACTIONABLE_LEVELS)
            ->get()
            ->each(function (SystemLog $log) use ($acknowledgements) {
                $acknowledgements->acknowledge(self::ACK_SOURCE, $log->id, [
                    'level' => $log->level,
                    'message' => $log->message,
                ]);
            });

        return redirect()
            ->route('system_logs.index')
            ->with('success', __('system-logs::messages.acknowledged_successfully'));
    }

    public function clear(): RedirectResponse
    {
        DB::table('wt_system_logs')->truncate();

        return redirect()
            ->route('system_logs.index')
            ->with('success', __('system-logs::messages.cleared_successfully'));
    }
}
