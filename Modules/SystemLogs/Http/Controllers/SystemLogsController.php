<?php

namespace Modules\SystemLogs\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\SystemLogs\Services\SystemLogsService;
use Illuminate\Http\Request;

class SystemLogsController extends Controller
{
    protected $logs;

    public function __construct(SystemLogsService $logs)
    {
        $this->logs = $logs;
    }

    public function index()
    {
        $logs = $this->logs->latest();
        return view('system-logs::Index', compact('logs'));
    }

    public function store(Request $request)
    {
        $this->logs->create(
            $request->input('level'),
            $request->input('message'),
            $request->input('context')
        );

        return redirect()->route('system_logs.index');
    }
}
