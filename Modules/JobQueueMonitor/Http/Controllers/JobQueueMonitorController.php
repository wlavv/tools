<?php

namespace Modules\JobQueueMonitor\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\JobQueueMonitor\Entities\JobQueueRun;
use Modules\JobQueueMonitor\Services\JobQueueMonitorService;

class JobQueueMonitorController extends Controller
{
    public function index(JobQueueMonitorService $service)
    {
        return $this->view('job-queue-monitor::dashboard.index', $service->dashboard());
    }

    public function failed()
    {
        $runs = JobQueueRun::openFailures()->latest('failed_at')->paginate(25);
        return $this->view('job-queue-monitor::failed.index', compact('runs'));
    }

    public function health(JobQueueMonitorService $service)
    {
        $health = $service->healthCheck();
        return $this->view('job-queue-monitor::health.index', compact('health'));
    }

    public function settings()
    {
        $config = config('job-queue-monitor');
        return $this->view('job-queue-monitor::settings.index', compact('config'));
    }

    public function show(JobQueueRun $run)
    {
        return $this->view('job-queue-monitor::dashboard.show', compact('run'));
    }

    public function resolve(Request $request, JobQueueRun $run, JobQueueMonitorService $service): RedirectResponse
    {
        $service->resolve($run, optional($request->user())->id, $request->input('resolution_note'));
        return back()->with('success', 'Falha marcada como resolvida.');
    }

    public function runHealthCheck(JobQueueMonitorService $service): RedirectResponse
    {
        $service->healthCheck();
        return back()->with('success', 'Health check executado.');
    }
}
