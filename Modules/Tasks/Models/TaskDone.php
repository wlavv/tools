<?php

namespace Modules\Tasks\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TaskDone extends Model
{
    use HasFactory;

    protected $table = 'wt_tasks_done';

    protected $fillable = [
        'type',
        'id_task',
        'name',
        'done',
        'value',
        'date',
    ];

    protected $casts = [
        'type' => 'integer',
        'done' => 'integer',
        'value' => 'decimal:2',
        'date' => 'date:Y-m-d',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'id_task', 'id');
    }

    public static function ensureMonthSetup(?int $year = null, ?int $month = null): void
    {
        static::syncMonthSetup($year, $month);
    }

    public static function syncMonthSetup(?int $year = null, ?int $month = null): void
    {
        TaskMember::bootstrapDefaults();
        if (class_exists(TaskRewardLevel::class)) {
            TaskRewardLevel::bootstrapDefaults();
        }

        $start = Carbon::create($year ?? now()->year, $month ?? now()->month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $tasks = Task::query()->get()->keyBy('id');
        if ($tasks->isEmpty()) {
            return;
        }

        $monthRows = static::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('id')
            ->get(['id', 'id_task', 'name', 'type', 'date', 'done']);

        $existing = collect();

        $monthRows
            ->groupBy(fn ($row) => $row->id_task . '|' . Carbon::parse($row->date)->toDateString())
            ->each(function ($group, $key) use (&$existing, $tasks) {
                $keeper = $group
                    ->sortByDesc(fn ($row) => ((int) $row->done * 1000000) + (int) $row->id)
                    ->first();

                $duplicates = $group->reject(fn ($row) => (int) $row->id === (int) $keeper->id)->pluck('id')->all();
                if (! empty($duplicates)) {
                    static::query()->whereIn('id', $duplicates)->delete();
                }

                $task = $tasks->get((int) $keeper->id_task);
                if ($task) {
                    $updates = [];
                    if ((string) $keeper->name !== (string) $task->name) {
                        $updates['name'] = $task->name;
                    }
                    if ((int) $keeper->type !== (int) $task->type) {
                        $updates['type'] = $task->type;
                    }
                    if (! empty($updates)) {
                        static::query()->whereKey($keeper->id)->update($updates);
                    }
                }

                $existing->put($key, (int) $keeper->id);
            });

        foreach ($existing as $key => $rowId) {
            [$taskId, $dateString] = explode('|', (string) $key, 2);
            $task = $tasks->get((int) $taskId);
            $date = Carbon::parse($dateString);
            $shouldExist = $task && ((int) ($task->is_active ?? 1) === 1) && $task->appliesToDate($date);

            if (! $shouldExist) {
                static::query()->whereKey($rowId)->delete();
                $existing->forget($key);
            }
        }

        $rows = [];
        foreach ($tasks as $task) {
            if ((int) ($task->is_active ?? 1) !== 1) {
                continue;
            }

            $day = $start->copy();
            while ($day <= $end) {
                $key = $task->id . '|' . $day->toDateString();
                if (! $existing->has($key) && $task->appliesToDate($day)) {
                    $rows[] = [
                        'type' => $task->type,
                        'id_task' => $task->id,
                        'name' => $task->name,
                        'done' => 0,
                        'value' => 0,
                        'date' => $day->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $day->addDay();
            }
        }

        if (! empty($rows)) {
            static::query()->insert($rows);
        }
    }

    public static function updateDoneForDate(array $payload, ?string $date = null): array
    {
        $targetDate = $date ?: now()->toDateString();

        $taskDone = static::query()
            ->where('id_task', $payload['id'])
            ->where('date', $targetDate)
            ->first();

        if (! $taskDone) {
            return ['success' => false, 'message' => 'Tarefa não encontrada para a data selecionada.'];
        }

        $task = Task::query()->find($payload['id']);
        if (! $task) {
            return ['success' => false, 'message' => 'Configuração da tarefa não encontrada.'];
        }

        $done = (int) ($payload['done'] ?? 0);
        $baseValue = (float) ($task->value ?? 0);
        $recordedValue = 0;

        if ($done === 1) {  $recordedValue = $task->isPenalty() ? -abs($baseValue) : abs($baseValue); }

        if ($done === -1) { $recordedValue = 0; }

        $taskDone->done = $done;
        $taskDone->value = $recordedValue;
        $taskDone->save();

        return [
            'success' => true,
            'done' => $done,
            'recorded_value' => $recordedValue,
            'counts_for_completion' => $task->countsForCompletion(),
            'value_mode' => $task->value_mode ?? 'add',
        ];
    }

    public static function getTasksOf(int $year, int $month): array
    {
        static::ensureMonthSetup($year, $month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $tasks = static::query()
            ->select(
                'wt_tasks_done.name as user_name',
                'wt_tasks_done.date',
                'wt_tasks.task as task_name',
                'wt_tasks.frequency',
                'wt_tasks.value_mode',
                'wt_tasks.counts_for_completion',
                'wt_tasks_done.done',
                'wt_tasks_done.value',
                'wt_tasks_done.type'
            )
            ->join('wt_tasks', 'wt_tasks_done.id_task', '=', 'wt_tasks.id')
            ->whereBetween('wt_tasks_done.date', [$startDate, $endDate])
            ->orderBy('wt_tasks_done.date')
            ->orderBy('wt_tasks_done.name')
            ->orderByRaw('COALESCE(wt_tasks.sort_order, 9999) asc')
            ->orderBy('wt_tasks.id')
            ->get();

        $calendar = [];
        foreach ($tasks as $task) {
            $date = Carbon::parse($task->date)->toDateString();
            $user = $task->user_name;
            $calendar[$date][$user][] = [
                'name' => $task->task_name,
                'done' => (int) $task->done,
                'value' => (float) $task->value,
                'type' => (int) $task->type,
                'frequency' => $task->frequency,
                'value_mode' => $task->value_mode,
                'counts_for_completion' => (int) ($task->counts_for_completion ?? 1),
            ];
        }

        ksort($calendar);
        return $calendar;
    }

    public static function getRewardDefinitions(int $year, int $month, ?int $memberId = null): Collection
    {
        TaskRewardLevel::bootstrapDefaults();

        if ($memberId) {
            $memberMonthlyOverrides = TaskRewardOverride::query()
                ->where('year', $year)
                ->where('month', $month)
                ->where('member_id', $memberId)
                ->where('is_active', 1)
                ->orderByRaw('COALESCE(sort_order, 9999) asc')
                ->orderBy('threshold_percent')
                ->get();
            if ($memberMonthlyOverrides->isNotEmpty()) {
                return $memberMonthlyOverrides->map(fn ($row) => (object) [
                    'threshold_percent' => (float) $row->threshold_percent,
                    'name' => $row->name,
                    'description' => $row->description,
                ])->values();
            }
        }

        $globalMonthlyOverrides = TaskRewardOverride::query()
            ->where('year', $year)
            ->where('month', $month)
            ->whereNull('member_id')
            ->where('is_active', 1)
            ->orderByRaw('COALESCE(sort_order, 9999) asc')
            ->orderBy('threshold_percent')
            ->get();
        if ($globalMonthlyOverrides->isNotEmpty()) {
            return $globalMonthlyOverrides->map(fn ($row) => (object) [
                'threshold_percent' => (float) $row->threshold_percent,
                'name' => $row->name,
                'description' => $row->description,
            ])->values();
        }

        if ($memberId && Schema::hasColumn('wt_task_reward_levels', 'member_id')) {
            $memberDefaults = TaskRewardLevel::query()
                ->where('is_active', 1)
                ->where('member_id', $memberId)
                ->orderByRaw('COALESCE(sort_order, 9999) asc')
                ->orderBy('threshold_percent')
                ->get(['threshold_percent', 'name', 'description']);
            if ($memberDefaults->isNotEmpty()) {
                return $memberDefaults;
            }
        }

        if (Schema::hasColumn('wt_task_reward_levels', 'member_id')) {
            return TaskRewardLevel::query()
                ->where('is_active', 1)
                ->whereNull('member_id')
                ->orderByRaw('COALESCE(sort_order, 9999) asc')
                ->orderBy('threshold_percent')
                ->get(['threshold_percent', 'name', 'description']);
        }

        return TaskRewardLevel::query()
            ->where('is_active', 1)
            ->orderByRaw('COALESCE(sort_order, 9999) asc')
            ->orderBy('threshold_percent')
            ->get(['threshold_percent', 'name', 'description']);
    }


    protected static function buildDailySeriesForMember(Collection $rows, Carbon $start, Carbon $end): Collection
    {
        $byDate = $rows->keyBy(fn ($row) => Carbon::parse($row->date)->toDateString());
        $series = collect();
        $day = $start->copy();
        while ($day <= $end) {
            $key = $day->toDateString();
            $row = $byDate->get($key);
            $done = (int) ($row->done_count ?? 0);
            $total = (int) ($row->total_count ?? 0);
            $percent = $total > 0 ? round(($done / $total) * 100, 1) : null;
            $series->push((object) [
                'date' => $key,
                'done_count' => $done,
                'total_count' => $total,
                'percent' => $percent,
                'label' => $day->format('d/m'),
                'weekday' => $day->translatedFormat('D'),
                'completed_day' => $total > 0 && $done >= $total,
            ]);
            $day->addDay();
        }
        return $series;
    }

    protected static function calculateStreaks(Collection $series): array
    {
        $current = 0;
        foreach ($series->reverse() as $day) {
            if (($day->completed_day ?? False) === True) {
                $current++;
                continue;
            }
            break;
        }

        $best = 0;
        $running = 0;
        foreach ($series as $day) {
            if (($day->completed_day ?? False) === True) {
                $running++;
                $best = max($best, $running);
            } else {
                $running = 0;
            }
        }

        return [
            'current' => $current,
            'best' => $best,
        ];
    }

    protected static function medalForPercent(float $percent): array
    {
        if ($percent >= 100) {
            return ['label' => 'Diamante', 'icon' => 'fa-solid fa-gem', 'class' => 'is-diamond'];
        }
        if ($percent >= 90) {
            return ['label' => 'Ouro', 'icon' => 'fa-solid fa-medal', 'class' => 'is-gold'];
        }
        if ($percent >= 75) {
            return ['label' => 'Prata', 'icon' => 'fa-solid fa-award', 'class' => 'is-silver'];
        }
        if ($percent >= 50) {
            return ['label' => 'Bronze', 'icon' => 'fa-solid fa-star', 'class' => 'is-bronze'];
        }

        return ['label' => 'Arranque', 'icon' => 'fa-regular fa-flag', 'class' => 'is-base'];
    }

    public static function getDashboardStats(int $year, int $month): array
    {
        static::ensureMonthSetup($year, $month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $memberStats = static::query()
            ->leftJoin('wt_task_members', 'wt_task_members.name', '=', 'wt_tasks_done.name')
            ->join('wt_tasks', 'wt_tasks.id', '=', 'wt_tasks_done.id_task')
            ->select(
                'wt_tasks_done.name',
                'wt_task_members.id as member_id',
                DB::raw('SUM(CASE WHEN COALESCE(wt_tasks.counts_for_completion, 1) = 1 THEN 1 ELSE 0 END) as total_rows'),
                DB::raw('SUM(CASE WHEN COALESCE(wt_tasks.counts_for_completion, 1) = 1 AND wt_tasks_done.done = 1 THEN 1 ELSE 0 END) as total_done'),
                DB::raw('SUM(CASE WHEN wt_tasks_done.done = 1 THEN wt_tasks_done.value ELSE 0 END) as total_value'),
                DB::raw('SUM(CASE WHEN COALESCE(wt_tasks.counts_for_completion, 1) = 0 THEN 1 ELSE 0 END) as penalty_rows'),
                DB::raw('SUM(CASE WHEN COALESCE(wt_tasks.counts_for_completion, 1) = 0 AND wt_tasks_done.done = 1 THEN 1 ELSE 0 END) as penalty_done')
            )
            ->whereBetween('wt_tasks_done.date', [$startDate, $endDate])
            ->groupBy('wt_tasks_done.name', 'wt_task_members.id')
            ->orderBy('wt_tasks_done.name')
            ->get()
            ->map(function ($row) use ($year, $month, $startDate, $endDate) {
                $totalRows = (int) $row->total_rows;
                $totalDone = (int) $row->total_done;
                $completionPercent = $totalRows > 0 ? round(($totalDone / $totalRows) * 100, 1) : 0.0;
                $rewardLevels = static::getRewardDefinitions($year, $month, $row->member_id ? (int) $row->member_id : null);

                $achieved = null;
                $nextLevel = null;
                $remainingTasksForNext = null;
                foreach ($rewardLevels as $level) {
                    if ($completionPercent >= (float) $level->threshold_percent) {
                        $achieved = $level;
                        continue;
                    }
                    if (! $nextLevel) {
                        $nextLevel = $level;
                        $requiredDone = (int) ceil(($totalRows * (float) $level->threshold_percent) / 100);
                        $remainingTasksForNext = max($requiredDone - $totalDone, 0);
                    }
                }

                $memberDailyRows = static::query()
                    ->join('wt_tasks', 'wt_tasks.id', '=', 'wt_tasks_done.id_task')
                    ->select(
                        'wt_tasks_done.date',
                        DB::raw('SUM(CASE WHEN COALESCE(wt_tasks.counts_for_completion, 1) = 1 AND wt_tasks_done.done = 1 THEN 1 ELSE 0 END) as done_count'),
                        DB::raw('SUM(CASE WHEN COALESCE(wt_tasks.counts_for_completion, 1) = 1 THEN 1 ELSE 0 END) as total_count')
                    )
                    ->where('wt_tasks_done.name', $row->name)
                    ->whereBetween('wt_tasks_done.date', [$startDate, $endDate])
                    ->groupBy('wt_tasks_done.date')
                    ->orderBy('wt_tasks_done.date')
                    ->get();

                $dailySeries = static::buildDailySeriesForMember($memberDailyRows, Carbon::parse($startDate), Carbon::parse($endDate));
                $streaks = static::calculateStreaks($dailySeries);
                $weekSeries = $dailySeries->slice(-7)->values();
                $activeDays = $dailySeries->filter(fn ($day) => (int) $day->total_count > 0);
                $daysCompleted = $activeDays->filter(fn ($day) => $day->completed_day)->count();
                $engagementPercent = $activeDays->count() > 0 ? round(($daysCompleted / $activeDays->count()) * 100, 1) : 0.0;

                $row->completion_percent = $completionPercent;
                $row->engagement_percent = $engagementPercent;
                $row->reward_levels = $rewardLevels;
                $row->reward_achieved = $achieved;
                $row->reward_next = $nextLevel;
                $row->remaining_tasks_for_next = $remainingTasksForNext;
                $row->progress_label = $totalDone . ' de ' . $totalRows;
                $row->daily_series = $dailySeries;
                $row->week_series = $weekSeries;
                $row->current_streak = $streaks['current'];
                $row->best_streak = $streaks['best'];
                $row->completed_days = $daysCompleted;
                $row->active_days = $activeDays->count();
                $row->medal = (object) static::medalForPercent($completionPercent);
                return $row;
            });

        $daily = static::query()
            ->join('wt_tasks', 'wt_tasks.id', '=', 'wt_tasks_done.id_task')
            ->select(
                'date',
                DB::raw('SUM(CASE WHEN COALESCE(wt_tasks.counts_for_completion, 1) = 1 AND wt_tasks_done.done = 1 THEN 1 ELSE 0 END) as done_count'),
                DB::raw('SUM(CASE WHEN COALESCE(wt_tasks.counts_for_completion, 1) = 1 THEN 1 ELSE 0 END) as total_count')
            )
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topConsistency = $memberStats->sortByDesc('current_streak')->sortByDesc('completion_percent')->values();
        $topCompletion = $memberStats->sortByDesc('completion_percent')->sortByDesc('current_streak')->values();
        $bestDay = collect($daily)->sortByDesc(function ($day) {
            $total = (int) ($day->total_count ?? 0);
            $done = (int) ($day->done_count ?? 0);
            return $total > 0 ? round(($done / $total) * 100, 4) : -1;
        })->first();

        return [
            'members' => $memberStats,
            'daily' => $daily,
            'rewardDefaults' => static::getRewardDefinitions($year, $month),
            'rankings' => [
                'consistency' => $topConsistency,
                'completion' => $topCompletion,
            ],
            'best_day' => $bestDay,
            'totals' => [
                'rows' => (int) $memberStats->sum('total_rows'),
                'done' => (int) $memberStats->sum('total_done'),
                'pending' => max((int) $memberStats->sum('total_rows') - (int) $memberStats->sum('total_done'), 0),
                'value' => (float) $memberStats->sum('total_value'),
            ],
        ];
    }

    public static function getTodayDoneMap(): Collection
    {
        return static::query()
            ->where('date', now()->toDateString())
            ->get(['id_task', 'done', 'value'])
            ->keyBy('id_task');
    }
}
