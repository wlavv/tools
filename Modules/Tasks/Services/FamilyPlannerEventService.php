<?php

namespace Modules\Tasks\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Tasks\Models\TaskEvent;

class FamilyPlannerEventService
{
    public function forMonth(Carbon $month): Collection
    {
        return TaskEvent::query()
            ->with('member')
            ->inMonth($month)
            ->orderBy('event_date')
            ->orderBy('event_time')
            ->get();
    }

    public function forDate(Carbon $date, ?int $memberId = null): Collection
    {
        return TaskEvent::query()
            ->with('member')
            ->onDate($date->toDateString())
            ->when($memberId, function ($query) use ($memberId) {
                $query->where(function ($q) use ($memberId) {
                    $q->whereNull('member_id')->orWhere('member_id', $memberId);
                });
            })
            ->orderBy('event_time')
            ->get();
    }

    public function create(array $data): TaskEvent
    {
        return TaskEvent::query()->create($data);
    }
}
