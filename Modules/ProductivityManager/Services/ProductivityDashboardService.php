<?php

namespace Modules\ProductivityManager\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductivityDashboardService
{
    public function getDashboardData(): array
    {
        return [
            'today' => $this->getTodayTasks(),
            'blocked' => $this->getBlockedTasks(),
            'alerts' => $this->getAlerts(),
            'projects' => $this->getProjectProgress(),
            'meta' => [
                'generated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ],
        ];
    }

    public function getTodayTasks()
    {
        return DB::table('wt_productivity_tasks')
            ->select('id', 'title', 'status', 'priority', 'project', 'due_date')
            ->whereIn('status', ['todo', 'doing', 'blocked'])
            ->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->orderBy('due_date')
            ->limit((int) config('productivitymanager.today_limit', 5))
            ->get();
    }

    public function getBlockedTasks()
    {
        return DB::table('wt_productivity_tasks')
            ->select('id', 'title', 'project', 'blocked_reason', 'blocked_by')
            ->where('status', 'blocked')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();
    }

    public function getAlerts()
    {
        return DB::table('wt_productivity_alerts')
            ->select('id', 'title', 'severity', 'source', 'created_at')
            ->where('is_active', 1)
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->orderByDesc('created_at')
            ->limit((int) config('productivitymanager.alert_limit', 8))
            ->get();
    }

    public function getProjectProgress()
    {
        return DB::table('wt_productivity_tasks')
            ->selectRaw('project, COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed")
            ->groupBy('project')
            ->orderBy('project')
            ->get()
            ->map(function ($row) {
                $row->progress = $row->total > 0 ? (int) round(($row->completed / $row->total) * 100) : 0;
                return $row;
            });
    }
}
