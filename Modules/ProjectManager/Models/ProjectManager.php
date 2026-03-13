<?php
namespace Modules\ProjectManager\Models;
use Illuminate\Database\Eloquent\Model;

class ProjectManager extends Model
{
    protected $table = 'wt_projects';

    protected $fillable = [
        'name','slug','status','priority','id_parent','url','website','logo','description',
        'repository_url','documentation_url','staging_url','production_url',
        'server_notes','deployment_notes','business_model','market_scope',
        'owner_name','owner_email','contact_name','contact_email','contact_phone',
        'primary_color','secondary_color','accent_color','font_family','brand_notes',
        'team_json','team_notes','structure_notes','documentation_notes',
        'goals','risks','next_steps','budget_notes','start_date','deadline'
    ];

    protected $casts = ['start_date' => 'date', 'deadline' => 'date'];

    public function children() { return $this->hasMany(self::class, 'id_parent')->orderBy('priority')->orderBy('name'); }
    public function tasks() { return $this->hasMany(ProjectTask::class, 'id_project')->orderBy('priority'); }
}
