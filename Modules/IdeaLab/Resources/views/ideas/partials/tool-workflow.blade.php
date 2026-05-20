@php
    $workflow = $toolWorkflow ?? [];
    $steps = $workflow['steps'] ?? [];
    $issues = $workflow['issues'] ?? [];
    $sandbox = $workflow['sandbox'] ?? [];
    $compliance = $workflow['compliance'] ?? [];
    $approval = $workflow['approval'] ?? [];
@endphp

<section class="card idealab-card idealab-section mb-3" id="tool-workflow">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong><i class="fa-solid fa-route me-1"></i> Tool Creation Workflow</strong>
        <span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $workflow['current'] ?? 'draft') }}</span>
    </div>
    <div class="card-body">
        <div class="idealab-workflow-strip mb-3">
            @foreach($steps as $index => $step)
                <div class="idealab-workflow-step {{ $step['done'] ? 'is-done' : '' }}">
                    <span>
                        @if($step['done'])
                            <i class="fa-solid fa-check"></i>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </span>
                    <strong>{{ $step['label'] }}</strong>
                    <small>{{ $step['detail'] }}</small>
                </div>
            @endforeach
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-4">
                <div class="border rounded p-3 h-100">
                    <div class="small text-muted">Sandbox</div>
                    <div class="fw-semibold">{{ $sandbox['module_name'] ?? 'Not created' }}</div>
                    <code class="small d-block text-wrap">{{ $sandbox['module_path'] ?? $workflow['sandbox_root'] ?? '-' }}</code>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="border rounded p-3 h-100">
                    <div class="small text-muted">Compliance</div>
                    <div class="fw-semibold">{{ $compliance['final_status'] ?? 'Not validated' }}</div>
                    <div class="small text-muted">
                        Score {{ $compliance['final_score'] ?? '-' }} ·
                        {{ $compliance['failed_findings'] ?? 0 }} failed ·
                        {{ $compliance['warning_findings'] ?? 0 }} warnings
                    </div>
                    @if(!empty($compliance['run_id']) && \Illuminate\Support\Facades\Route::has('module_compliance_center.runs.show'))
                        <a class="btn btn-sm btn-outline-primary mt-2" href="{{ route('module_compliance_center.runs.show', $compliance['run_id']) }}">
                            <i class="fa-solid fa-eye"></i> Open run
                        </a>
                    @endif
                </div>
            </div>
            <div class="col-lg-4">
                <div class="border rounded p-3 h-100">
                    <div class="small text-muted">Go live</div>
                    <div class="fw-semibold">{{ !empty($approval['approved_at']) ? 'Approved' : 'Blocked until approval' }}</div>
                    <div class="small text-muted">{{ $approval['approved_at'] ?? 'Sandbox/Staging only' }}</div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('idealab.workflow.request_changes', $idea) }}" class="mb-3">
            @csrf
            <label class="form-label small text-muted">Reprovar ou pedir reformulacao</label>
            <div class="input-group">
                <input type="text" name="reason" class="form-control" placeholder="Problema a enviar no proximo pedido ao AI Consensus">
                <button class="btn btn-outline-warning">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Request changes
                </button>
            </div>
        </form>

        @if(!empty($issues))
            <div class="idealab-issue-list mb-3">
                @foreach($issues as $issue)
                    <div class="idealab-issue-row">
                        <span class="badge bg-warning text-dark">{{ $issue['severity'] ?? 'warning' }}</span>
                        <code class="ms-1">{{ $issue['code'] ?? 'ISSUE' }}</code>
                        <div class="mt-1">{{ $issue['message'] ?? '-' }}</div>
                        @if(!empty($issue['file_path']))
                            <code class="small d-block mt-1">{{ $issue['file_path'] }}</code>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('idealab.workflow.discussion', $idea) }}">
                @csrf
                <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-brain me-1"></i> AI discussion</button>
            </form>
            <form method="POST" action="{{ route('idealab.workflow.blueprint', $idea) }}">
                @csrf
                <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-cubes me-1"></i> Generate blueprint</button>
            </form>
            <form method="POST" action="{{ route('idealab.workflow.sandbox', $idea) }}">
                @csrf
                <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-flask me-1"></i> Create sandbox</button>
            </form>
            <form method="POST" action="{{ route('idealab.workflow.compliance', $idea) }}">
                @csrf
                <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-shield-halved me-1"></i> Run compliance</button>
            </form>
            @if(!empty($issues))
                <form method="POST" action="{{ route('idealab.workflow.reformulate', $idea) }}">
                    @csrf
                    <button class="btn btn-outline-warning btn-sm"><i class="fa-solid fa-rotate me-1"></i> Send issues to AI</button>
                </form>
            @endif
            <form method="POST" action="{{ route('idealab.workflow.approve_go_live', $idea) }}">
                @csrf
                <button class="btn btn-outline-success btn-sm"><i class="fa-solid fa-check me-1"></i> Approve go live</button>
            </form>
        </div>
    </div>
</section>
