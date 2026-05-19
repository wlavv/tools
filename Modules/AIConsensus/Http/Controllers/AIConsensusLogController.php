<?php

namespace Modules\AIConsensus\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\AIConsensus\Models\AIConsensusLog;

class AIConsensusLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AIConsensusLog::query()
            ->with('run')
            ->when($request->filled('run_id'), fn ($query) => $query->where('run_id', $request->integer('run_id')))
            ->latest('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('ai-consensus::logs.index', compact('logs'));
    }
}
