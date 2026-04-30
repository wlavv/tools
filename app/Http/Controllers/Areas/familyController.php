<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class familyController extends Controller{

    protected bool $hasPageActions = false;

    public function index(){

        notifications_send([
            'title' => 'Acesso a familyController',
            'message' => 'O utilizador acedeu a familyController em ' . date('y-m-d'),
            'type' => 'info',
            'category' => 'system',
            'priority' => 'normal',
            'channels' => ['internal'],
            'users'    => [1]
        ]);

        $this->addAccess( route('tasks.index'),         'Tarefas',      'fa-solid fa-list-check' );
        $this->addAccess( route('budget.index'),        'Budget',       'fa-solid fa-euro-sign' );
        /**$this->addAccess( route('investments.index'),   "Investments",  'fa-solid fa-money-bill-trend-up',  );**/
        return $this->view('areas/family/index');

    }
    
}
