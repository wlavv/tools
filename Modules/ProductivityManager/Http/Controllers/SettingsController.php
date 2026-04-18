<?php

namespace Modules\ProductivityManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class SettingsController extends Controller{

    public function __construct(){
    
        $this->middleware('auth');
        $this->setIndexPage('productivity manager', 'productivityManager.index');
        /** 
        $this->breadcrumbs[] = ['name' => 'administration', 'url' => route('administration.index')];
        $this->breadcrumbs[] = ['name' => 'productivity manager', 'url' => route('productivityManager.index')];
        $this->breadcrumbs[] = ['name' => 'settings', 'url' => route('productivityManager.settings')];
        **/
    }

    public function index(): View{

        $data = [
            'actions' => $this->actions,
            'breadcrumbs' => $this->breadcrumbs,
            'config' => config('productivitymanager'),
        ];

        return $this->view('productivitymanager::settings.index', $data );
    }
}
