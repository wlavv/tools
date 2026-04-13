<?php

namespace Modules\Tasks\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyPlannerThought extends Model
{
    protected $table = 'wt_task_family_planner_thoughts';

    protected $fillable = [
        'thought_date',
        'quote',
        'author',
        'source',
        'is_fallback',
        'raw_quote',
        'raw_language',
        'translated_language',
    ];

    protected $casts = [
        'thought_date' => 'date',
        'is_fallback' => 'boolean',
    ];
}
