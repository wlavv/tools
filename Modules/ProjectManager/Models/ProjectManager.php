<?php

namespace Modules\ProjectManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectManager extends Model
{
    protected $table = 'wt_projects';

    protected $fillable = [
        'name',
        'slug',
        'status',
        'priority',
        'id_parent',
        'url',
        'logo',
        'description',
        'primary_color',
        'secondary_color',
        'accent_color',
        'font_family',
        'brand_notes',
        'contact_name',
        'contact_email',
        'contact_phone',
        'website',
        'social_facebook',
        'social_instagram',
        'social_linkedin',
        'social_youtube',
        'repository_url',
        'documentation_url',
        'team_notes',
        'team_json',
        'structure_notes',
        'documentation_notes',
        'start_date',
        'deadline',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'id_parent');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'id_parent')->orderBy('priority');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'id_project')->orderBy('priority');
    }

    public function rootTasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'id_project')
            ->where('id_parent', 0)
            ->orderBy('priority');
    }
}
