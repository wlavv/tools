<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class uploadsController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        //$this->breadcrumbs[] = [ 'name' =>  'suppliersBackorders', 'url' => route('suppliersBackorders.index')];
    }

    public function index()
    {
        $data = [
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs
        ];

        return View::make('customTools/suppliersBackorders/index')->with($data);
    }

    public function create(){ }
    public function store(Request $request){ }
    public function show(string $id){ }
    public function edit(string $id){ }
    public function update(Request $request, string $id){ }
    public function destroy(string $id){ }
    
    public function upload(Request $request){
        
        if ($request->file('file')) {
            $file = $request->file('file');
            
            Storage::disk('public_uploads')->put($request->upload_path.$request->upload_filename, file_get_contents($file));
            return 1;
        }
        
        return 0;
    }
}
