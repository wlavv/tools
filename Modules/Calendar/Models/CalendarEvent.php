<?php

namespace Modules\Calendar\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $table = 'calendar_events';

    protected $fillable = [
        'context_id',
        'category_id',
        'title',
        'description',
        'location',
        'start_at',
        'end_at',
        'all_day',
        'status',
        'reference_type',
        'reference_id',
        'created_by',
    ];

    protected $casts = [
        'all_day' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function context()
    {
        return $this->belongsTo(CalendarContext::class, 'context_id');
    }

    public function category()
    {
        return $this->belongsTo(CalendarCategory::class, 'category_id');
    }
}
