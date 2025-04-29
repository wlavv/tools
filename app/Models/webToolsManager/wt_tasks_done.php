<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

use App\Models\webToolsManager\wt_tasks;

class wt_tasks_done extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct(){
        $this->table = "wt_tasks_done";
    }

    public function task()
    {
        return $this->hasOne(wt_tasks::class, 'id', 'id_task'); // Definimos a relação com o id_task
    }

    public static function checkInitialSetup(){

        $ano = now()->year;
        $mes = now()->month;
        $inicioMes = Carbon::create( date('Y'), date('m'), 1);
        $fimMes = $inicioMes->copy()->endOfMonth();
    
        $existe = wt_tasks_done::where('date', date('Y-m-d'))->exists();
        if ($existe)  return; 
    
        $tarefas = wt_tasks::all();
    
        $registos = [];
    
        foreach ($tarefas as $tarefa) {
            $dia = $inicioMes->copy();
            while ($dia <= $fimMes) {
                $registos[] = [
                    'type' => $tarefa->type,
                    'id_task' => $tarefa->id,
                    'name' => $tarefa->name,
                    'done' => 0,
                    'value' => 0,
                    'date' => $dia->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $dia->addDay();
            }
        }
    
        wt_tasks_done::insert($registos);

    }
    
    public static function updateDone($request){

        $task_done = wt_tasks_done::where('id_task', $request->id)->where('date', date('Y-m-d'))->first();

        if (!$task_done) {
            return response()->json(['success' => false, 'message' => 'Tarefa não encontrada.'], 404);
        }

        if($request->type == 2) $task_done->value = $request->value;
        $task_done->done = $request->done;
        $task_done->save();

        return 1;
    }

    public static function getTasksOf($year, $month){

        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();  // Primeiro dia do mês
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $tasks = self::select(
            'wt_tasks_done.name as user_name',
            'wt_tasks_done.date',
            'wt_tasks.task as task_name',
            'wt_tasks_done.done',
            'wt_tasks_done.value',
            'wt_tasks_done.type'
        )
        ->join('wt_tasks', 'wt_tasks_done.id_task', '=', 'wt_tasks.id')
        ->whereBetween('wt_tasks_done.date', [$startDate, $endDate])
        ->orderBy('wt_tasks_done.date')
        ->orderBy('wt_tasks_done.name')
        ->get();

        $calendar = [];

        foreach ($tasks as $task) {

            $date = $task->date;
            $user = $task->user_name;

            $calendar[$date][$user][] = [
                'name' => $task->task_name,
                'done' => $task->done,
                'value' => $task->value,
                'type' => $task->type,
            ];
        }

        return $calendar;
    }
}
