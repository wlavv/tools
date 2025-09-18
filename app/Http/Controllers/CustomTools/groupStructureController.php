<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\prestashop\product;

use App\Models\webToolsManager\wt_projects;

class groupStructureController extends customToolsController
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  'Life-Style', 'url' => route('groupStructure.index')];
    }

    public function index()
    {
        
        $data = [
            'actions'    => $this->actions,
            'counters'   => [],
            'breadcrumbs'=> $this->breadcrumbs,
            'accessList' => [],
            'projects' => wt_projects::getProjects()
        ];


        $this->setViewData($data);
        
        return View::make('areas/administration/index')->with($this->viewData);
    }

    public function newProject(Request $request){
        
        if(isset($request->project_id)){
            return wt_projects::editProject($request);
        }else{
            return wt_projects::addProject($request);
        }
    }

    public function destroy(Request $request) {
        return wt_projects::destroyRow($request->id);
    }
    
    public function edit($id){
        
        $project = wt_projects::find($id);
    
        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Projeto não encontrado.'
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'project' => [
                'name'       => $project->name,
                'id_parent'  => $project->id_parent,
                'priority'   => $project->priority,
                'status'     => $project->status,
                'url'        => $project->url,
                'logo'       => $project->logo
            ]
        ]);
    }

    
}
