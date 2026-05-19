<?php

namespace Modules\IdeaLab\Models;

use Illuminate\Database\Eloquent\Model;

class IdeaActivityLog extends Model
{
    protected $table = 'idealab_activity_logs';
    protected $guarded = ['id'];
    protected $casts = ['context' => 'array'];


    public function idea()
    {
        return $this->belongsTo(Idea::class, 'idea_id');
    }

}
