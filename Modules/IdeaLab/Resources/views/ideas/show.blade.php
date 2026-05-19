@extends('idealab::layouts.master')

@section('idealab-content')
@include('idealab::partials.alerts')

@php
    $centralLinks = $idea->aiConsensusRuns->sortByDesc('created_at');
    $centralRuns = $centralLinks->pluck('aiConsensusRun')->filter();
    $totalCost = $centralRuns->sum(fn ($run) => $run->providerResponses->sum(fn ($response) => (float) ($response->cost_estimate ?? 0)));
    $latestRun = $centralRuns->first();
    $tagsValue = $idea->tags->pluck('name')->implode(', ');
@endphp

<div class="idealab-summary">
    <div class="idealab-summary-item">
        <span>Status</span>
        <strong>{{ config('idealab.statuses.' . $idea->status, $idea->status) }}</strong>
    </div>
    <div class="idealab-summary-item">
        <span>Priority</span>
        <strong>{{ config('idealab.priorities.' . $idea->priority, $idea->priority) }}</strong>
    </div>
    <div class="idealab-summary-item">
        <span>Readiness</span>
        <strong>{{ $idea->readiness_label }}</strong>
    </div>
    <div class="idealab-summary-item">
        <span>Score</span>
        <strong>{{ $idea->final_score ?? '-' }}</strong>
    </div>
    <div class="idealab-summary-item">
        <span>AI Runs</span>
        <strong>{{ $centralRuns->count() }}</strong>
    </div>
    <div class="idealab-summary-item">
        <span>AI Cost</span>
        <strong>${{ number_format($totalCost, 4) }}</strong>
    </div>
</div>

<div class="idealab-layout">
    <aside class="idealab-side">
        <div class="card idealab-card mb-3">
            <div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action" href="#ai-consensus"><i class="fa-solid fa-brain me-1"></i> AI Consensus</a>
                <a class="list-group-item list-group-item-action" href="#overview"><i class="fa-solid fa-circle-info me-1"></i> Overview</a>
                <a class="list-group-item list-group-item-action" href="#history"><i class="fa-solid fa-clock-rotate-left me-1"></i> Project Manager</a>
            </div>
        </div>

        <div class="card idealab-card mb-3">
            <div class="card-header bg-white"><strong>Contexto</strong></div>
            <div class="card-body">
                <div class="small text-muted">Categoria</div>
                <div class="mb-2">{{ $idea->category?->name ?? 'Sem categoria' }}</div>
                <div class="small text-muted">Origem</div>
                <div class="mb-2">{{ config('idealab.sources.' . $idea->source, $idea->source) }}</div>
                <div class="small text-muted">Tags</div>
                <div class="mb-2">{{ $tagsValue ?: '-' }}</div>
                <div class="small text-muted">Último AI Run</div>
                <div>{{ $latestRun?->status ?? '-' }}</div>
            </div>
        </div>

        <div class="card idealab-card mb-3" id="actions">
            <div class="card-header bg-white"><strong>Estado</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('idealab.update', $idea) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="title" value="{{ $idea->title }}">
                    <input type="hidden" name="description_raw" value="{{ $idea->description_raw }}">
                    <input type="hidden" name="category_id" value="{{ $idea->category_id }}">
                    <input type="hidden" name="priority" value="{{ $idea->priority }}">
                    <input type="hidden" name="source" value="{{ $idea->source }}">
                    <input type="hidden" name="tags" value="{{ $tagsValue }}">

                    <label class="form-label small text-muted">Alterar estado</label>
                    <select name="status" class="form-select form-select-sm mb-2">
                        @foreach(config('idealab.statuses', []) as $key => $label)
                            <option value="{{ $key }}" @selected($idea->status === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-primary w-100">
                        <i class="fa-solid fa-save"></i> Guardar estado
                    </button>
                </form>
            </div>
        </div>

        <div class="card idealab-card">
            <div class="card-header bg-white"><strong>Fluxo</strong></div>
            <div class="card-body d-grid gap-2">
                <form method="POST" action="{{ route('idealab.ai.run', $idea) }}">
                    @csrf
                    <input type="hidden" name="template_key" value="project_conversion_brief">
                    <input type="hidden" name="mode" value="template">
                    <button class="btn btn-outline-primary btn-sm w-100">
                        <i class="fa-solid fa-diagram-project"></i> Preparar plano PM
                    </button>
                </form>
                <form method="POST" action="{{ route('idealab.convert', $idea) }}">
                    @csrf
                    <button class="btn btn-success btn-sm w-100">
                        <i class="fa-solid fa-arrow-right"></i> Criar Project Manager
                    </button>
                </form>
                <form method="POST" action="{{ route('idealab.ai.run', $idea) }}">
                    @csrf
                    <input type="hidden" name="template_key" value="module_blueprint">
                    <input type="hidden" name="mode" value="template">
                    <button class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fa-solid fa-cubes"></i> Blueprint LSG v1
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main>
        <section class="card idealab-card idealab-section mb-3" id="ai-consensus">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>AI Consensus</strong>
                <div class="text-muted small">
                    {{ $centralRuns->where('status', 'completed')->count() }} completed · ${{ number_format($totalCost, 4) }}
                </div>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-responses" type="button">Respostas</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-chat" type="button">Chat</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-runs" type="button">Runs</button></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-responses">
                        <div class="idealab-chat-box">
                            @forelse($centralLinks as $link)
                                @php
                                    $centralRun = $link->aiConsensusRun;
                                    $output = $centralRun?->outputs?->last();
                                    $finalContent = $output?->content ?: $centralRun?->final_output;
                                    $runCost = $centralRun?->providerResponses?->sum(fn ($response) => (float) ($response->cost_estimate ?? 0)) ?? 0;
                                @endphp

                                @if($centralRun)
                                    <div class="idealab-chat-message system">
                                        <strong>#{{ $centralRun->id }} {{ $centralRun->template?->name ?? $centralRun->title }}</strong>
                                        <div class="idealab-chat-meta">
                                            <span>{{ $link->purpose }}</span>
                                            <span>{{ $centralRun->status }}</span>
                                            <span>{{ $centralRun->output_type }}</span>
                                            <span>${{ number_format($runCost, 4) }}</span>
                                            <span>{{ $centralRun->created_at?->format('Y-m-d H:i') }}</span>
                                        </div>
                                        @if(in_array($centralRun->status, ['pending', 'failed'], true))
                                            <form method="POST" action="{{ route('ai_consensus.runs.process', $centralRun) }}" class="mt-2">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success">
                                                    <i class="fa-solid fa-play"></i> Processar agora
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <details class="mb-2">
                                        <summary class="fw-semibold">Respostas dos providers ({{ $centralRun->providerResponses->count() }})</summary>
                                        @foreach($centralRun->providerResponses as $providerResponse)
                                            <div class="idealab-chat-message {{ $providerResponse->status === 'completed' ? 'provider' : 'failed' }} mt-2">
                                                <strong>{{ $providerResponse->provider?->name ?? 'Internal provider' }}</strong>
                                                <div class="idealab-chat-meta">
                                                    <span>{{ $providerResponse->status }}</span>
                                                    <span>score {{ $providerResponse->score ?? '-' }}</span>
                                                    <span>${{ number_format((float) ($providerResponse->cost_estimate ?? 0), 4) }}</span>
                                                    <span>{{ $providerResponse->latency_ms ?? '-' }} ms</span>
                                                </div>
                                                <pre>{{ $providerResponse->raw_response ?: $providerResponse->error_message }}</pre>
                                            </div>
                                        @endforeach
                                    </details>

                                    <div class="idealab-chat-message assistant">
                                        <strong>Resposta consolidada</strong>
                                        <div class="idealab-chat-meta">
                                            <span>{{ $output?->format ?? data_get($centralRun->options, 'return_format', 'json') }}</span>
                                            <span>schema {{ $output?->schema_valid ? 'valid' : 'pending/invalid' }}</span>
                                            <span>cost ${{ number_format($runCost, 4) }}</span>
                                            @if($centralRun->final_score)<span>score {{ $centralRun->final_score }}</span>@endif
                                        </div>
                                        <pre>{{ $finalContent ?: $centralRun->error_message ?: 'Run ainda sem resposta consolidada.' }}</pre>
                                        <a href="{{ route('ai_consensus.runs.show', $centralRun) }}" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fa-solid fa-up-right-from-square"></i> Abrir no AI Consensus
                                        </a>
                                        @if($centralRun->final_output)
                                            <a href="{{ route('ai_consensus.runs.download', $centralRun) }}" class="btn btn-sm btn-outline-secondary mt-2">
                                                <i class="fa-solid fa-download"></i> Download output
                                            </a>
                                            @if($centralRun->output_type === 'lsg_module_blueprint')
                                                <form method="POST" action="{{ route('ai_consensus.runs.module_package', $centralRun) }}" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-primary mt-2">
                                                        <i class="fa-solid fa-box-archive"></i> Download pacote módulo
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                @endif
                            @empty
                                <div class="text-muted">Ainda não há respostas AI Consensus para esta ideia.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-chat">
                        <div class="idealab-chat-box mb-3">
                            @forelse($idea->aiMessages->sortBy('created_at') as $message)
                                <div class="idealab-chat-message {{ $message->role }}">
                                    <strong>{{ ucfirst($message->role) }}</strong><br>
                                    {!! nl2br(e($message->content)) !!}
                                </div>
                            @empty
                                <div class="text-muted">Sem mensagens manuais ainda.</div>
                            @endforelse
                        </div>
                        <form method="POST" action="{{ route('idealab.ai.run', $idea) }}">
                            @csrf
                            <input type="hidden" name="mode" value="chat">
                            <div class="input-group">
                                <select name="template_id" class="form-select" style="max-width: 260px;">
                                    <option value="">Default template</option>
                                    @foreach(\Modules\IdeaLab\Models\IdeaAiTemplate::query()->where('is_active', true)->orderBy('sort_order')->get() as $template)
                                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="message" class="form-control" placeholder="Pergunta ou instrução">
                                <button class="btn btn-primary"><i class="fa-solid fa-brain"></i></button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="tab-runs">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Type</th><th>Status</th><th>Created</th><th>AI Consensus</th><th>Error</th></tr></thead>
                                <tbody>
                                    @forelse($idea->aiRuns as $run)
                                        <tr>
                                            <td>{{ $run->run_type }}</td>
                                            <td>{{ $run->status }}</td>
                                            <td>{{ $run->created_at?->format('Y-m-d H:i') }}</td>
                                            <td>
                                                @if(data_get($run->response_payload, 'ai_consensus_route'))
                                                    <a href="{{ data_get($run->response_payload, 'ai_consensus_route') }}" class="btn btn-sm btn-outline-primary">
                                                        #{{ data_get($run->response_payload, 'ai_consensus_run_id') }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $run->error_message ? str($run->error_message)->limit(120) : '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-muted">Sem runs.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="card idealab-card idealab-section mb-3" id="overview">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Overview</strong>
                <span class="badge bg-light text-dark border">{{ $idea->category?->name ?? 'Sem categoria' }}</span>
            </div>
            <div class="card-body">
                <h6>Descrição original</h6>
                <p class="text-muted mb-3">{!! nl2br(e($idea->description_raw)) !!}</p>
                @if($idea->description_refined)
                    <h6>Descrição refinada</h6>
                    <p class="mb-0">{!! nl2br(e($idea->description_refined)) !!}</p>
                @endif
            </div>
        </section>

        <section class="card idealab-card idealab-section" id="history">
            <div class="card-header bg-white"><strong>Project Manager / Conversões</strong></div>
            <div class="card-body">
                @forelse($idea->conversions as $conversion)
                    <div class="border rounded p-2 mb-2">
                        <strong>{{ $conversion->status }}</strong>
                        <small class="text-muted ms-2">{{ $conversion->created_at?->format('Y-m-d H:i') }}</small>
                        @if($conversion->project_id)
                            <div><a href="{{ route('project_manager.projects.show', $conversion->project_id) }}">Open Project #{{ $conversion->project_id }}</a></div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">Esta ideia ainda não foi convertida.</p>
                @endforelse
            </div>
        </section>
    </main>
</div>
@endsection
