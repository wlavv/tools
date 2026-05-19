@extends('idealab::layouts.master')

@section('idealab-content')
@include('idealab::partials.alerts')

<div class="idealab-context-strip mb-3">
    <span><i class="fa-solid fa-layer-group"></i> {{ $idea->category?->name ?? 'Uncategorized' }}</span>
    <span><i class="fa-solid fa-circle-half-stroke"></i> {{ config('idealab.statuses.' . $idea->status, $idea->status) }}</span>
    <span><i class="fa-solid fa-flag"></i> {{ config('idealab.priorities.' . $idea->priority, $idea->priority) }}</span>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card idealab-card mb-3">
            <div class="card-header bg-white"><strong>Idea Overview</strong></div>
            <div class="card-body">
                <h6>Raw Description</h6>
                <p class="text-muted">{!! nl2br(e($idea->description_raw)) !!}</p>
                @if($idea->description_refined)
                    <h6>Refined Description</h6>
                    <p>{!! nl2br(e($idea->description_refined)) !!}</p>
                @endif
                <div class="mt-3">
                    @foreach($idea->tags as $tag)
                        <span class="badge bg-light text-dark border me-1">{{ $tag->name }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card idealab-card mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>AI Consensus Chat / Template Run</strong>
            </div>
            <div class="card-body">
                <div class="idealab-chat-box mb-3">
                    @forelse($idea->aiMessages->sortBy('created_at') as $message)
                        <div class="idealab-chat-message {{ $message->role }}">
                            <strong>{{ ucfirst($message->role) }}</strong><br>
                            {!! nl2br(e($message->content)) !!}
                        </div>
                    @empty
                        @if($idea->aiConsensusRuns->isEmpty())
                            <div class="text-muted">No chat messages yet. Use a template or send a structured message to AI Consensus.</div>
                        @endif
                    @endforelse

                    @foreach($idea->aiConsensusRuns->sortBy('created_at') as $link)
                        @php
                            $centralRun = $link->aiConsensusRun;
                            $output = $centralRun?->outputs?->last();
                            $finalContent = $output?->content ?: $centralRun?->final_output;
                            $runCost = $centralRun?->providerResponses?->sum(fn ($response) => (float) ($response->cost_estimate ?? 0)) ?? 0;
                        @endphp

                        @if($centralRun)
                            <div class="idealab-chat-message system">
                                <strong>AI Consensus Run #{{ $centralRun->id }}</strong>
                                <div>{{ $centralRun->template?->name ?? $centralRun->title }}</div>
                                <div class="idealab-chat-meta">
                                    <span>{{ $link->purpose }}</span>
                                    <span>{{ $centralRun->status }}</span>
                                    <span>{{ $centralRun->output_type }}</span>
                                    <span>${{ number_format($runCost, 4) }}</span>
                                    <span>{{ $centralRun->created_at?->format('Y-m-d H:i') }}</span>
                                </div>
                            </div>

                            @foreach($centralRun->providerResponses as $providerResponse)
                                <div class="idealab-chat-message {{ $providerResponse->status === 'completed' ? 'provider' : 'failed' }}">
                                    <strong>{{ $providerResponse->provider?->name ?? 'Internal provider' }}</strong>
                                    <div class="idealab-chat-meta">
                                        <span>{{ $providerResponse->status }}</span>
                                        <span>score {{ $providerResponse->score ?? '-' }}</span>
                                        <span>${{ number_format((float) ($providerResponse->cost_estimate ?? 0), 4) }}</span>
                                        <span>{{ $providerResponse->latency_ms ?? '-' }} ms</span>
                                    </div>
                                    @if($providerResponse->raw_response)
                                        <pre>{{ $providerResponse->raw_response }}</pre>
                                    @elseif($providerResponse->error_message)
                                        <pre>{{ $providerResponse->error_message }}</pre>
                                    @endif
                                </div>
                            @endforeach

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
                            </div>
                        @endif
                    @endforeach
                </div>
                <form method="POST" action="{{ route('idealab.ai.run', $idea) }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-4">
                            <select name="template_id" class="form-select">
                                <option value="">Default template</option>
                                @foreach(\Modules\IdeaLab\Models\IdeaAiTemplate::query()->where('is_active', true)->orderBy('sort_order')->get() as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="mode" class="form-select">
                                <option value="template">Template</option>
                                <option value="chat">Chat</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="message" class="form-control" placeholder="Optional question or instruction">
                        </div>
                    </div>
                    <button class="btn btn-primary mt-2"><i class="fa-solid fa-brain me-1"></i> Create AI Consensus Payload</button>
                </form>
            </div>
        </div>

        <div class="card idealab-card">
            <div class="card-header bg-white"><strong>AI Runs</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Type</th><th>Status</th><th>Template</th><th>Created</th><th>AI Consensus</th><th>Payload</th></tr></thead>
                    <tbody>
                        @forelse($idea->aiRuns as $run)
                            <tr>
                                <td>{{ $run->run_type }}</td>
                                <td>{{ $run->status }}</td>
                                <td>{{ $run->template?->name ?? '-' }}</td>
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
                                <td><code>{{ str($run->prompt_text)->limit(80) }}</code></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-muted">No AI runs yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card idealab-card idealab-soft mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Final Score</span>
                    <span class="idealab-score">{{ $idea->final_score ?? '-' }}</span>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item bg-transparent d-flex justify-content-between"><span>Opportunity</span><strong>{{ $idea->opportunity_score ?? '-' }}</strong></li>
                    <li class="list-group-item bg-transparent d-flex justify-content-between"><span>Effort</span><strong>{{ $idea->effort_score ?? '-' }}</strong></li>
                    <li class="list-group-item bg-transparent d-flex justify-content-between"><span>Risk</span><strong>{{ $idea->risk_score ?? '-' }}</strong></li>
                    <li class="list-group-item bg-transparent d-flex justify-content-between"><span>Strategic</span><strong>{{ $idea->strategic_score ?? '-' }}</strong></li>
                    <li class="list-group-item bg-transparent d-flex justify-content-between"><span>Reusability</span><strong>{{ $idea->reusability_score ?? '-' }}</strong></li>
                    <li class="list-group-item bg-transparent d-flex justify-content-between"><span>Monetization</span><strong>{{ $idea->monetization_score ?? '-' }}</strong></li>
                </ul>
            </div>
        </div>

        <div class="card idealab-card mb-3">
            <div class="card-header bg-white"><strong>Conversion</strong></div>
            <div class="card-body">
                @forelse($idea->conversions as $conversion)
                    <div class="border rounded p-2 mb-2">
                        <strong>{{ $conversion->status }}</strong><br>
                        <small class="text-muted">{{ $conversion->created_at?->format('Y-m-d H:i') }}</small>
                        @if($conversion->project_id)
                            <div><a href="{{ route('project_manager.projects.show', $conversion->project_id) }}">Open Project #{{ $conversion->project_id }}</a></div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">This idea has not been converted yet.</p>
                @endforelse
            </div>
        </div>

        <div class="card idealab-card">
            <div class="card-header bg-white"><strong>API Entry Point</strong></div>
            <div class="card-body">
                <code>{{ route('api.idealab.ideas.ai-payload', $idea) }}</code>
            </div>
        </div>
    </div>
</div>
@endsection
