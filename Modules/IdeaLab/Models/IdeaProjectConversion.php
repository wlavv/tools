<?php

namespace Modules\IdeaLab\Models;

use Illuminate\Database\Eloquent\Model;

class IdeaProjectConversion extends Model
{
    protected $table = 'idealab_project_conversions';
    protected $guarded = ['id'];
    protected $casts = ['conversion_payload' => 'array'];


    public function idea()
    {
        return $this->belongsTo(Idea::class, 'idea_id');
    }

}
