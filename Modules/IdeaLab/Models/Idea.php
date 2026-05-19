<?php

namespace Modules\IdeaLab\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Idea extends Model
{
    use SoftDeletes;

    protected $table = 'idealab_ideas';
    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'array',
        'converted_at' => 'datetime',
        'final_score' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(IdeaCategory::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(IdeaTag::class, 'idealab_idea_tag', 'idea_id', 'tag_id')->withTimestamps();
    }

    public function aiRuns()
    {
        return $this->hasMany(IdeaAiRun::class, 'idea_id')->latest();
    }

    public function aiConsensusRuns()
    {
        return $this->hasMany(IdeaAiConsensusRun::class, 'idea_id')->latest();
    }

    public function aiMessages()
    {
        return $this->hasMany(IdeaAiMessage::class, 'idea_id')->latest();
    }

    public function conversions()
    {
        return $this->hasMany(IdeaProjectConversion::class, 'idea_id')->latest();
    }

    public function activityLogs()
    {
        return $this->hasMany(IdeaActivityLog::class, 'idea_id')->latest();
    }

    public function getReadinessLabelAttribute(): string
    {
        if ($this->status === 'converted') {
            return 'Converted';
        }

        if (($this->final_score ?? 0) >= 75 && in_array($this->status, ['refined', 'candidate_project', 'approved'], true)) {
            return 'Ready';
        }

        if (($this->final_score ?? 0) >= 50) {
            return 'Needs review';
        }

        return 'Early stage';
    }
}
