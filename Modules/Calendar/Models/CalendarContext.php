<?php

namespace Modules\Calendar\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarContext extends Model
{
    protected $table = 'calendar_contexts';

    protected $fillable = [
        'name',
        'slug',
        'color',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function categories()
    {
        return $this->hasMany(CalendarCategory::class, 'context_id');
    }

    public function events()
    {
        return $this->hasMany(CalendarEvent::class, 'context_id');
    }
}
