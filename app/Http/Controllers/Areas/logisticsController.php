<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class logisticsController extends Controller
{
    protected bool $hasPageActions = false;

    public function index()
    {
        return $this->view('areas/logistics/index');
    }
}
