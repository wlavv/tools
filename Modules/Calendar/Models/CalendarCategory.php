<?php

namespace Modules\Calendar\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarCategory extends Model
{
    protected $table = 'calendar_categories';

    protected $fillable = [
        'context_id',
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

    public function context()
    {
        return $this->belongsTo(CalendarContext::class, 'context_id');
    }

    public function events()
    {
        return $this->hasMany(CalendarEvent::class, 'category_id');
    }
}
