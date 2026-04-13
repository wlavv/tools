<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class hrController extends Controller{
    
    public function index(){
        $this->setIndexPage('hr', 'hr.index');
        $this->addAccess( route('calendar.index'), 'Calendar', 'fa-regular fa-calendar' );
        return $this->view('areas/hr/index');
    }

    public function create(){ dd('NOT IMPLEMENTED!'); }
    public function store(Request $request){ dd('NOT IMPLEMENTED!'); }
    public function show($id){ dd('NOT IMPLEMENTED!'); }
    public function edit($id){ dd('NOT IMPLEMENTED!'); }
    public function update(Request $request, $id){ dd('NOT IMPLEMENTED!'); }
    public function destroy($id){ dd('NOT IMPLEMENTED!'); }

}