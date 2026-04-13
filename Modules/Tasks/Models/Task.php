<?php

namespace Modules\Tasks\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $table = 'wt_tasks';

    protected $fillable = [
        'member_id',
        'type',
        'name',
        'task',
        'image',
        'value',
        'sort_order',
        'is_active',
        'days_mask',
        'frequency',
        'monthly_day',
        'counts_for_completion',
        'value_mode',
    ];

    protected $casts = [
        'type' => 'integer',
        'value' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'integer',
        'monthly_day' => 'integer',
        'counts_for_completion' => 'integer',
    ];

    public $timestamps = false;

    public function member(): BelongsTo
    {
        return $this->belongsTo(TaskMember::class, 'member_id', 'id');
    }

    public function doneEntries(): HasMany
    {
        return $this->hasMany(TaskDone::class, 'id_task', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('is_active')->orWhere('is_active', 1);
        });
    }

    public function isPenalty(): bool
    {
        return ($this->value_mode ?? 'add') === 'subtract';
    }

    public function countsForCompletion(): bool
    {
        return (int) ($this->counts_for_completion ?? 1) === 1;
    }

    public function frequencyLabel(): string
    {
        return match ($this->frequency ?? 'daily') {
            'weekly' => 'Semanal',
            'monthly' => 'Mensal',
            default => 'Diária',
        };
    }

    public function executionLabel(): string
    {
        $frequency = $this->frequency ?? 'daily';
        if ($frequency === 'weekly') {
            $labels = $this->selectedDaysLabels();
            return empty($labels) ? 'Sem dias definidos' : implode(' · ', $labels);
        }

        if ($frequency === 'monthly') {
            return 'Dia ' . (int) ($this->monthly_day ?: 1);
        }

        return 'Diário';
    }

    public static function weekdayOptions(): array
    {
        return [
            1 => '2ª',
            2 => '3ª',
            3 => '4ª',
            4 => '5ª',
            5 => '6ª',
            6 => 'Sáb',
            7 => 'Dom',
        ];
    }

    public static function parseDaysMask($raw): array
    {
        $map = [
            '1' => 1, 'mon' => 1, 'monday' => 1, 'seg' => 1, 'segunda' => 1,
            '2' => 2, 'tue' => 2, 'tuesday' => 2, 'ter' => 2, 'terca' => 2, 'terça' => 2,
            '3' => 3, 'wed' => 3, 'wednesday' => 3, 'qua' => 3, 'quarta' => 3,
            '4' => 4, 'thu' => 4, 'thursday' => 4, 'qui' => 4, 'quinta' => 4,
            '5' => 5, 'fri' => 5, 'friday' => 5, 'sex' => 5, 'sexta' => 5,
            '6' => 6, 'sat' => 6, 'saturday' => 6, 'sab' => 6, 'sábado' => 6, 'sabado' => 6,
            '7' => 7, 'sun' => 7, 'sunday' => 7, 'dom' => 7, 'domingo' => 7,
        ];

        if (is_null($raw) || $raw === '') {
            return [];
        }

        $values = is_array($raw) ? $raw : preg_split('/[,;|\s]+/', (string) $raw, -1, PREG_SPLIT_NO_EMPTY);

        $days = [];
        foreach ($values as $value) {
            $key = mb_strtolower(trim((string) $value));
            if ($key === '') {
                continue;
            }
            if (isset($map[$key])) {
                $days[] = $map[$key];
                continue;
            }
            if (is_numeric($key)) {
                $int = (int) $key;
                if ($int >= 1 && $int <= 7) {
                    $days[] = $int;
                }
            }
        }

        $days = array_values(array_unique($days));
        sort($days);
        return $days;
    }

    public static function normalizeDaysMaskValue($raw): ?string
    {
        $days = static::parseDaysMask($raw);
        return empty($days) ? null : implode(',', $days);
    }

    public function selectedDays(): array
    {
        return static::parseDaysMask($this->days_mask);
    }

    public function selectedDaysLabels(): array
    {
        $labels = static::weekdayOptions();
        return collect($this->selectedDays())
            ->map(fn (int $day) => $labels[$day] ?? (string) $day)
            ->values()
            ->all();
    }

    public function appliesToDate($date): bool
    {
        if (! $date instanceof CarbonInterface) {
            $date = now()->parse($date);
        }

        $frequency = $this->frequency ?? 'daily';

        if ($frequency === 'weekly') {
            $allowed = $this->selectedDays();
            if (empty($allowed)) {
                return false;
            }

            return in_array((int) $date->dayOfWeekIso, $allowed, true);
        }

        if ($frequency === 'monthly') {
            $monthlyDay = (int) ($this->monthly_day ?: 1);
            $monthlyDay = min(max($monthlyDay, 1), (int) $date->copy()->endOfMonth()->day);

            return (int) $date->day === $monthlyDay;
        }

        return true;
    }

    public static function getGroupedForDate($date = null): array
    {
        $date = $date ? now()->parse($date) : now();

        $members = TaskMember::query()
            ->active()
            ->orderByRaw('COALESCE(sort_order, 9999) asc')
            ->orderBy('id')
            ->get();

        $result = [];

        foreach ($members as $member) {
            $tasks = static::query()
                ->active()
                ->where(function ($q) use ($member) {
                    $q->where('member_id', $member->id)
                        ->orWhere(function ($q2) use ($member) {
                            $q2->whereNull('member_id')
                                ->where('name', $member->name);
                        });
                })
                ->orderByRaw('COALESCE(sort_order, 9999) asc')
                ->orderBy('id')
                ->get()
                ->filter(fn (Task $task) => $task->appliesToDate($date))
                ->values();

            $result[$member->name] = $tasks;
        }

        return $result;
    }

    public static function getGroupedForToday(): array
    {
        return static::getGroupedForDate(now());
    }
}
