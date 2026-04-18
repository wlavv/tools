<?php

namespace Modules\SystemLogs\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\SystemLogs\Services\SystemLogsService;
use Illuminate\Http\Request;

class SystemLogsController extends Controller
{
    protected $logs;

    public function __construct( SystemLogsService $logs ) {
        $this->middleware('auth');
        $this->setIndexPage('system-logs', 'system_logs.index');
        $this->logs = $logs;

    }

    public function index(){

        return $this->view('system-logs::Index', ['logs' => $this->logs->latest()]);
    }

    public function store(Request $request){

        $this->logs->create(
            $request->input('level'),
            $request->input('message'),
            $request->input('context')
        );

        return redirect()->route('system_logs.index');
    }
}
