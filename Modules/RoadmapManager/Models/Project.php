<?php

namespace Modules\RoadmapManager\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'wt_projects';

    protected $fillable = [
        'id_parent','have_details','name','status','priority','url','logo','slogan','theme',
        'main_color_1','main_color_2','main_color_3','support_color_1','support_color_2','support_color_3',
        'email_signature','email_1','email_2','email_3','email_4','email_5','email_6','email_7','slug','description',
        'primary_color','secondary_color','accent_color','font_family','brand_notes','contact_name','contact_email',
        'contact_phone','website','social_facebook','social_instagram','social_linkedin','social_youtube',
        'repository_url','documentation_url','team_notes','team_json','structure_notes','documentation_notes',
        'start_date','deadline'
    ];

    public function roadmapGroups()
    {
        return $this->belongsToMany(ProjectGroup::class, 'wt_project_group_links', 'project_id', 'roadmap_group_id');
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class, 'project_id');
    }

    public function tasks()
    {
        return $this->hasMany(TaskItem::class, 'project_id');
    }
}
