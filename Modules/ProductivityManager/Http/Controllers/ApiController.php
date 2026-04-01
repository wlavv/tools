<?php

namespace Modules\ProductivityManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\ProductivityManager\Services\ProductivityDashboardService;

class ApiController extends Controller
{
    public function __construct(
        protected ProductivityDashboardService $dashboardService
    ) {
        $this->middleware('auth');
    }

    public function dashboard(): JsonResponse
    {
        return response()->json($this->dashboardService->getDashboardData());
    }

    public function storeTask(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'project' => 'nullable|string|max:120',
            'priority' => 'nullable|in:low,medium,high,critical',
            'source' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $id = DB::table('wt_productivity_tasks')->insertGetId([
            'title' => $data['title'],
            'project' => $data['project'] ?? 'General',
            'status' => 'todo',
            'priority' => $data['priority'] ?? 'medium',
            'source' => $data['source'] ?? 'manual',
            'notes' => $data['notes'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function completeTask(Request $request): JsonResponse
    {
        $data = $request->validate([
            'task_id' => 'required|integer',
        ]);

        DB::table('wt_productivity_tasks')
            ->where('id', $data['task_id'])
            ->update([
                'status' => 'done',
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    public function blockTask(Request $request): JsonResponse
    {
        $data = $request->validate([
            'task_id' => 'required|integer',
            'blocked_reason' => 'nullable|string',
            'blocked_by' => 'nullable|string|max:120',
        ]);

        DB::table('wt_productivity_tasks')
            ->where('id', $data['task_id'])
            ->update([
                'status' => 'blocked',
                'blocked_reason' => $data['blocked_reason'] ?? null,
                'blocked_by' => $data['blocked_by'] ?? null,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    public function createAlert(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'severity' => 'nullable|in:low,medium,high,critical',
            'source' => 'nullable|string|max:120',
        ]);

        $id = DB::table('wt_productivity_alerts')->insertGetId([
            'title' => $data['title'],
            'severity' => $data['severity'] ?? 'medium',
            'source' => $data['source'] ?? 'manual',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }
}
