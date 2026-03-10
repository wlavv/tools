<?php

namespace Modules\ProjectManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectTask extends Model
{
    protected $table = 'wt_todo';

    protected $fillable = [
        'id_project',
        'title',
        'priority',
        'status',
        'start_date',
        'expected_time',
        'id_parent',
        'comment',
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectManager::class, 'id_project');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'id_parent');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'id_parent')->orderBy('priority');
    }
}
