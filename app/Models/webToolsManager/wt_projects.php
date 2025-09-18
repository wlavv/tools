<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\webToolsManager\wt_todo;

class wt_projects extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct(){
        $this->table = env('DB2_prefix')."projects";
    }

    public function tasks()
    {
        return $this->hasMany(wt_todo::class, 'id_project', 'id')->where('status', 0)->where('id_parent', '>', 0);
    }
    
    public static function getProjects($status = 9){
        
        $projects = array();
        
        $fathers = self::getProjectsFather($status);
        
        foreach($fathers AS $father){
            $sons = self::getProjectSons($father->id, $status);
            
            $projects[] = [
                'father' => $father,
                'sons'   => $sons
            ];
        }
        return $projects;
    }
    
    public static function getProjectsFather($status = 9){
        return ($status = 9) ? self::where('id_parent', 0)->orderBy('priority', 'ASC')->get() : self::where('id_parent', 0)->where('status', $status)->orderBy('priority', 'ASC')->get();
    }
    
    public static function getProjectSons($id_parent, $status = 9){
        return ($status = 9) ? self::with('tasks')->where('id_parent', $id_parent)->orderBy('priority', 'ASC')->get() : self::where('id_parent', $id_parent)->where('status', $status)->orderBy('priority', 'ASC')->get();
    }
    
    public static function addProject($data){
        
        $project = new wt_projects();
        $project->name = $data->project_name;
        $project->url = $data->project_url;
        $project->id_parent = $data->id_parent;
        $project->priority = $data->project_priority;
        $project->status = $data->project_status;
        $project->logo = ( strlen($data->project_logo) > 0 ) ? $data->project_logo : '/images/logos/unknown.png';
        $project->save();

        return 1;
    }
    
    public static function editProject($data){
        
        $project = wt_projects::where('id', $data->project_id)->first();
        $project->name = $data->project_name;
        $project->url = $data->project_url;
        $project->id_parent = $data->id_parent;
        $project->priority = $data->project_priority;
        $project->status = $data->project_status;
        $project->logo = ( strlen($data->project_logo) > 0 ) ? $data->project_logo : '/images/logos/unknown.png';
        $project->save();

        return 1;
    }

    public static function destroyRow($id){
        
        $project = wt_projects::find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Projeto não encontrado.'
            ], 404);
        }

        try {
            $project->delete();

            return response()->json([
                'success' => true,
                'message' => 'Projeto excluído com sucesso.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir o projeto: ' . $e->getMessage()
            ], 500);
        }
    }
    
}
