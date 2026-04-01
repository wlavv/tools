<?php

namespace Modules\RoadmapManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectGroup extends Model
{
    use SoftDeletes;

    protected $table = 'wt_roadmap_groups';

    protected $fillable = [
        'uuid', 'name', 'slug', 'description', 'color', 'icon', 'status', 'sort_order'
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'wt_project_group_links', 'roadmap_group_id', 'project_id');
    }
}
