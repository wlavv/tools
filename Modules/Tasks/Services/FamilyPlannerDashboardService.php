<?php

namespace Modules\Tasks\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskDone;
use Modules\Tasks\Models\TaskMember;
use Modules\Tasks\Models\TaskRewardLevel;

class FamilyPlannerDashboardService
{
    public function build(Carbon $selectedDate, ?string $selectedMemberSlug = null): array
    {
        TaskMember::bootstrapDefaults();
        TaskRewardLevel::bootstrapDefaults();
        TaskDone::ensureMonthSetup($selectedDate->year, $selectedDate->month);

        $members = TaskMember::query()
            ->active()
            ->orderByRaw('COALESCE(sort_order, 9999) asc')
            ->orderBy('id')
            ->get();

        $memberKeys = $members->mapWithKeys(function ($member) {
            return [
                $member->name => ($member->slug ?: Str::slug(Str::ascii($member->name), '_')),
            ];
        })->toArray();

        $selectedMember = $members->first(function ($member) use ($selectedMemberSlug, $memberKeys) {
            $slug = $memberKeys[$member->name] ?? ($member->slug ?: Str::slug(Str::ascii($member->name), '_'));
            return $selectedMemberSlug && $slug === $selectedMemberSlug;
        });

        $groupedTasks = Task::getGroupedForDate($selectedDate);
        $todayDoneMap = TaskDone::query()
            ->where('date', $selectedDate->toDateString())
            ->get(['id_task', 'done', 'value'])
            ->keyBy('id_task');

        $currentStats = collect(TaskDone::getDashboardStats($selectedDate->year, $selectedDate->month)['members'] ?? [])->keyBy('name');

        $previousMonth = $selectedDate->copy()->subMonthNoOverflow();
        $previousStats = collect(TaskDone::getDashboardStats($previousMonth->year, $previousMonth->month)['members'] ?? [])->keyBy('name');

        $memberSummaries = $members->map(function ($member) use ($memberKeys, $currentStats, $previousStats, $groupedTasks, $todayDoneMap) {
            $slug = $memberKeys[$member->name] ?? ($member->slug ?: Str::slug(Str::ascii($member->name), '_'));
            $stat = $currentStats[$member->name] ?? null;
            $prev = $previousStats[$member->name] ?? null;
            $tasksToday = collect($groupedTasks[$member->name] ?? []);

            $doneToday = $tasksToday->filter(function ($task) use ($todayDoneMap) {
                if (!isset($todayDoneMap[$task->id])) return false;

                $status = (int) $todayDoneMap[$task->id]->done;

                // ✔ conta OK e NÃO OK
                return in_array($status, [1, -1]);
            })->count();

            return (object) [
                'member' => $member,
                'slug' => $slug,
                'today_total' => $tasksToday->count(),
                'today_done' => $doneToday,
                'today_pending' => max($tasksToday->count() - $doneToday, 0),
                'month_total' => (int) ($stat->total_rows ?? 0),
                'month_done' => (int) ($stat->total_done ?? 0),
                'month_pending' => max((int) ($stat->total_rows ?? 0) - (int) ($stat->total_done ?? 0), 0),
                'month_percent' => (float) ($stat->completion_percent ?? 0),
                'current_value' => (float) ($stat->total_value ?? 0),
                'previous_value' => (float) ($prev->total_value ?? 0),
                'misses' => max((int) ($stat->total_rows ?? 0) - (int) ($stat->total_done ?? 0), 0),
                'reward_levels' => $stat->reward_levels ?? collect(),
                'reward_achieved' => $stat->reward_achieved ?? null,
                'reward_next' => $stat->reward_next ?? null,
                'remaining_tasks_for_next' => (int) ($stat->remaining_tasks_for_next ?? 0),
            ];
        });

        $selectedSummary = $selectedMember
            ? $memberSummaries->firstWhere('member.id', $selectedMember->id)
            : null;

$selectedTasks = $selectedMember
    ? collect($groupedTasks[$selectedMember->name] ?? [])->map(function ($task) use ($todayDoneMap) {
        $doneInfo = $todayDoneMap[$task->id] ?? null;
        $responseStatus = (int) ($doneInfo->done ?? 0);

        return (object) [
            'id' => $task->id,
            'title' => $task->task,
            'image' => $task->image,

            // ✔ feito
            'done' => $responseStatus === 1,

            // ✔ novo estado
            'response_status' => $responseStatus,

            // ✔ respondido (OK ou NÃO OK)
            'responded' => in_array($responseStatus, [1, -1]),

            'value' => (float) ($doneInfo->value ?? 0),
        ];
    })->values()
    : collect();

        return [
            'members' => $members,
            'memberKeys' => $memberKeys,
            'selectedMember' => $selectedMember,
            'selectedSummary' => $selectedSummary,
            'memberSummaries' => $memberSummaries,
            'selectedTasks' => $selectedTasks,
            'groupedTasks' => $groupedTasks,
            'todayDoneMap' => $todayDoneMap,
            'currentStats' => $currentStats,
            'previousStats' => $previousStats,
        ];
    }
}
