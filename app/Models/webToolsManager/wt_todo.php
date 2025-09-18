<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wt_todo extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_project',
        'title',
        'priority',
        'status',
        'start_date',
        'expected_time',
        'id_parent',
        'comment'
    ];
    public $timestamps = false;

    public function __construct(){
        $this->table = env('DB2_prefix')."todo";
    }

    public static function addTask($request){
        
        if (!empty($request->id)) {
            $task = self::find($request->id);
            if ($task) {
                $task->update([
                    'id_project'    => $request->id_project,
                    'title'         => $request->title,
                    'priority'      => $request->priority,
                    'status'        => $request->status,
                    'start_date'    => $request->start_date,
                    'expected_time' => $request->expected_time,
                    'id_parent'     => $request->id_parent ?? 0,
                    'comment'       => $request->comment ?? null,
                ]);
                return $task;
            }
        }
    
        // Cria nova tarefa
        return self::create([
            'id_project'    => $request->id_project,
            'title'         => $request->title,
            'priority'      => $request->priority,
            'status'        => $request->status,
            'start_date'    => $request->start_date,
            'expected_time' => $request->expected_time,
            'id_parent'     => $request->id_parent ?? 0,
            'comment'       => $request->comment ?? null,
        ]);


    }
    
    public static function getTasksStructured($id_project){
        
        $mainTasks = self::where('id_project', $id_project)->where('id_parent', 0)->where('status', 0)->orderBy('priority')->get();
        $tasks = $mainTasks->map(function ($task) {
            return self::buildTree($task); 
        });
        return $tasks;
    }
    
    private static function buildTree($task){

        $children = self::where('id_parent', $task->id)->where('status', 0)->orderBy('priority')->get();
        $task->children = $children->map(function ($child) {
            return self::buildTree($child);
        });
        return $task;
    }

}
