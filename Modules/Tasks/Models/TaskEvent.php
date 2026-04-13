<?php

namespace Modules\Tasks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskEvent extends Model
{
    use HasFactory;

    protected $table = 'wt_task_events';

    protected $fillable = [
        'member_id',
        'title',
        'description',
        'event_date',
        'event_time',
        'color',
        'icon',
    ];

    protected $casts = [
        'member_id' => 'integer',
        'event_date' => 'date:Y-m-d',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(TaskMember::class, 'member_id', 'id');
    }

    public function scopeInMonth($query, $month)
    {
        $start = $month->copy()->startOfMonth()->toDateString();
        $end = $month->copy()->endOfMonth()->toDateString();

        return $query->whereBetween('event_date', [$start, $end]);
    }

    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('event_date', $date);
    }
}
