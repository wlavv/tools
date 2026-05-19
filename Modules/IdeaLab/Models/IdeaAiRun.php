<?php

namespace Modules\IdeaLab\Models;

use Illuminate\Database\Eloquent\Model;

class IdeaAiRun extends Model
{
    protected $table = 'idealab_ai_runs';
    protected $guarded = ['id'];
    protected $casts = ['prompt_payload' => 'array', 'response_payload' => 'array', 'scores' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime'];


    public function idea()
    {
        return $this->belongsTo(Idea::class, 'idea_id');
    }

    public function template()
    {
        return $this->belongsTo(IdeaAiTemplate::class, 'template_id');
    }

}
