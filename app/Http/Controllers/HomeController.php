<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller{
    
    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){
        dd(12);
        return view('dashboard');
    }
}
