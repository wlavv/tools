<?php

namespace Modules\IdeaLab\Models;

use Illuminate\Database\Eloquent\Model;

class IdeaTag extends Model
{
    protected $table = 'idealab_tags';
    protected $guarded = ['id'];
    protected $casts = [];


    public function ideas()
    {
        return $this->belongsToMany(Idea::class, 'idealab_idea_tag', 'tag_id', 'idea_id')->withTimestamps();
    }

}
