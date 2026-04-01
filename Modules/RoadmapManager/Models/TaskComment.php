<?php

namespace Modules\RoadmapManager\Models;

use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    protected $table = 'wt_task_comments';

    protected $fillable = ['task_id','user_id','parent_id','content','mentions'];


}
