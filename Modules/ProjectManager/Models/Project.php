<?php

namespace Modules\ProjectManager\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'wt_projects';
    protected $guarded = [];

    protected $fillable = [];
    public $timestamps = true;

    protected $casts = [
        'is_pinned' => 'boolean',
        'start_date' => 'date',
        'deadline' => 'date',
        'progress_percent' => 'decimal:2',
    ];
}
