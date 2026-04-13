<?php

namespace Modules\Tasks\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class FamilyPlannerCalendarService
{
    public function build(Carbon $selectedDate, Collection $events, ?int $selectedMemberId = null): array
    {
        $month = $selectedDate->copy()->startOfMonth();
        $start = $month->copy()->startOfWeek(Carbon::MONDAY);
        $end = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $days = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $iso = $date->toDateString();

            $dayEvents = $events
                ->filter(function ($event) use ($iso, $selectedMemberId) {
                    $eventDate = $event->event_date instanceof Carbon
                        ? $event->event_date->toDateString()
                        : (string) $event->event_date;

                    if ($eventDate !== $iso) {
                        return false;
                    }

                    if ($selectedMemberId && $event->member_id && (int) $event->member_id !== $selectedMemberId) {
                        return false;
                    }

                    return true;
                })
                ->values();

            $eventDots = $dayEvents
                ->map(function ($event) {
                    $isFamily = empty($event->member_id);

                    return [
                        'type' => $isFamily ? 'family' : 'member',
                        'color' => $isFamily
                            ? '#d6b16b'
                            : ($event->member->color ?? '#89b6df'),
                        'label' => $isFamily
                            ? 'Família'
                            : ($event->member->name ?? 'Membro'),
                    ];
                })
                ->unique(function (array $dot) {
                    return $dot['type'] . '|' . $dot['label'] . '|' . $dot['color'];
                })
                ->values()
                ->all();

            $days[] = [
                'date' => $iso,
                'day' => $date->day,
                'is_current_month' => $date->month === $month->month,
                'is_selected' => $iso === $selectedDate->toDateString(),
                'is_today' => $iso === now()->toDateString(),
                'event_count' => count($eventDots),
                'event_dots' => $eventDots,
            ];
        }

        return [
            'month' => $month,
            'days' => $days,
            'selected' => $selectedDate,
        ];
    }
}
