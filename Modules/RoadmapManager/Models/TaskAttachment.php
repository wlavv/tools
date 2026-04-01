<?php

namespace Modules\RoadmapManager\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    protected $table = 'wt_task_attachments';

    protected $fillable = ['task_id','user_id','disk','path','filename','mime_type','size'];


}
