<?php

namespace Modules\ModuleHealth\Http\Controllers;

use App\Http\Controllers\Controller;

class ModuleHealthProfileController extends Controller
{
    public function index()
    {
        $profiles = config('module-health.profiles', []);

        return $this->view('module-health::profiles.index', compact('profiles'));
    }
}
