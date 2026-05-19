<?php

namespace Modules\IdeaLab\Models;

use Illuminate\Database\Eloquent\Model;

class IdeaAiMessage extends Model
{
    protected $table = 'idealab_ai_messages';
    protected $guarded = ['id'];
    protected $casts = ['payload' => 'array'];


    public function idea()
    {
        return $this->belongsTo(Idea::class, 'idea_id');
    }

    public function aiRun()
    {
        return $this->belongsTo(IdeaAiRun::class, 'ai_run_id');
    }

}
