<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\webToolsManager\wt_todo;
use App\Models\webToolsManager\wt_projects;

class todoController extends customToolsController
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct(){
        
        $this->middleware('auth');
    }

    public function index($id){
        
        $this->breadcrumbs[] = [ 'name' =>  'todo', 'url' => route('todo.index', ['id' => $id])];
        
        $data = [
            'breadcrumbs'=> $this->breadcrumbs,
            'id_project' => $id,
            'mainTasks'    => wt_todo::getTasksStructured($id)
        ];
        
        $this->setViewData($data);
        return View::make('customTools/todo/index')->with( $this->viewData );
    }

    public function store(Request $request) {
        return wt_todo::addTask($request);
    } 
    
    public function saveOrder(Request $request)
    {
        $order = $request->input('order', []);

        $this->updateTaskOrder($order, null);

        return response()->json(['status' => 'success']);
    }

    private function updateTaskOrder(array $tasks, $parentId = null)
    {
        foreach ($tasks as $index => $task) {
            $taskModel = wt_todo::find($task['id']);
            if ($taskModel) {
                $taskModel->id_parent = $parentId;
                $taskModel->priority = $index;
                $taskModel->save();

                if (isset($task['children']) && is_array($task['children']) && count($task['children']) > 0) {
                    $this->updateTaskOrder($task['children'], $task['id']);
                }
            }
        }
    }

    public function destroy($id)
    {
        $task = wt_todo::find($id);

        if (!$task) {
            return response()->json(['status' => 'error', 'message' => 'Task not found'], 404);
        }

        $task->delete();

        return response()->json(['status' => 'success']);
    }  

    public function setDone($id)
    {
        $task = wt_todo::find($id);

        if (!$task) {
            return response()->json(['status' => 'error', 'message' => 'Task not found'], 404);
        }

        $task->update(['status' => 1]);

        return response()->json(['status' => 'success']);
    }    
    
}
