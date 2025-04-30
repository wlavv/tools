<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use App\Models\webToolsManager\wt_tasks;
use App\Models\webToolsManager\wt_tasks_done;

class tasksController extends Controller
{
    public $actions;
    public $breadcrumbs;

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  'tasks', 'url' => route('tasks.index')];
    }

    public function index()
    {
        wt_tasks_done::checkInitialSetup();

        $data = [
            'counters'      => [],
            'panels'        => null,
            'accessList'    => [],
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs,
            'tasks'         => [
                                'Márcia' => wt_tasks::getTasks(1, 'Márcia'),
                                'Bruno'  => wt_tasks::getTasks(1, 'Bruno'),
                                'Inês'   => wt_tasks::getTasks(2, 'Inês'),
                                'Eva'    => wt_tasks::getTasks(2, 'Eva'),                                
                            ]
        ];

        return View::make('areas/tasks/index')->with($data);
    }

    public function updateDone(Request $request){
        return response()->json(['success' => true, 'done' => wt_tasks_done::updateDone($request)]);
    }

    public function getMonthInfo($year, $month)
    {
        $calendar = wt_tasks_done::getTasksOf($year, $month);

        $this->breadcrumbs[] = [ 'name' =>  'tasks', 'url' => route('tasks.index')];
        $this->breadcrumbs[] = [ 'name' =>  'tasks', 'url' => route('tasks.index')];

        $data = [
            'breadcrumbs'   => $this->breadcrumbs,
        ];

        return view('areas.tasks.calendar', compact('data', 'calendar', 'year', 'month'));
    }
    
}
