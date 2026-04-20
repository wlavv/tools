@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div class="ai-consensus-page">
    @include('ai-consensus::Includes.css')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="ai-counters">
        <div class="ai-counter">
            <div class="ai-counter-label">Estado</div>
            <div class="ai-counter-value">{{ ucfirst($run->status ?? 'n/a') }}</div>
        </div>

        <div class="ai-counter">
            <div class="ai-counter-label">Template</div>
            <div class="ai-counter-value">{{ $run->template_key ?: 'n/a' }}</div>
        </div>

        <div class="ai-counter">
            <div class="ai-counter-label">Provider final</div>
            <div class="ai-counter-value">{{ $run->final_provider ?: 'n/a' }}</div>
        </div>

        <div class="ai-counter">
            <div class="ai-counter-label">Modelo final</div>
            <div class="ai-counter-value">{{ $run->final_model ?: 'n/a' }}</div>
        </div>

        <div class="ai-counter ai-counter--highlight">
            <div class="ai-counter-label">Custo</div>
            <div class="ai-counter-value">${{ number_format((float) $run->total_cost_estimate_usd, 4) }}</div>
        </div>
    </div>

    <div class="ai-collapse-card">
        <button type="button" class="ai-collapse-toggle" data-ai-collapse-toggle aria-expanded="false">
            <span class="ai-collapse-title">Prompt original</span>
            <span class="ai-collapse-icon"><i class="fa-solid fa-chevron-down"></i></span>
        </button>
        <div class="ai-collapse-body" data-ai-collapse-body hidden>
            <div class="ai-card">
                <div class="ai-pre">{{ $run->prompt }}</div>
            </div>
        </div>
    </div>

    <div class="ai-collapse-card">
        <button type="button" class="ai-collapse-toggle" data-ai-collapse-toggle aria-expanded="false">
            <span class="ai-collapse-title">Resumo técnico</span>
            <span class="ai-collapse-icon"><i class="fa-solid fa-chevron-down"></i></span>
        </button>
        <div class="ai-collapse-body" data-ai-collapse-body hidden>
            <div class="ai-card">
                <div class="small ai-muted">Tokens entrada: {{ (int) $run->total_tokens_in }}</div>
                <div class="small ai-muted">Tokens saída: {{ (int) $run->total_tokens_out }}</div>
                <div class="small ai-muted">Ficheiros: {{ $run->files->count() }}</div>
                <div class="small ai-muted">Respostas: {{ $run->responses->count() }}</div>
            </div>
        </div>
    </div>

    @if(count($run->files) > 0)
        <div class="ai-collapse-card">
            <button type="button" class="ai-collapse-toggle" data-ai-collapse-toggle aria-expanded="false">
                <span class="ai-collapse-title">Ficheiros anexados</span>
                <span class="ai-collapse-icon"><i class="fa-solid fa-chevron-down"></i></span>
            </button>
            <div class="ai-collapse-body" data-ai-collapse-body hidden>
                <div class="ai-card">
                    <div class="ai-files-list">
                        @forelse($run->files as $file)
                            <div class="ai-provider-box">
                                <strong>{{ $file->original_name }}</strong>
                                <div class="small ai-muted">MIME: {{ $file->mime_type ?: 'n/a' }}</div>
                                <div class="small ai-muted">Status: {{ $file->status }}</div>
                                <div class="small ai-muted">Tamanho: {{ number_format(((int) $file->size_bytes) / 1024, 2) }} KB</div>
                            </div>
                        @empty
                            <div class="ai-muted">Sem anexos.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($parsedFilesPreview)
        <div class="ai-collapse-card">
            <button type="button" class="ai-collapse-toggle" data-ai-collapse-toggle aria-expanded="false">
                <span class="ai-collapse-title">Conteúdo extraído dos ficheiros</span>
                <span class="ai-collapse-icon"><i class="fa-solid fa-chevron-down"></i></span>
            </button>
            <div class="ai-collapse-body" data-ai-collapse-body hidden>
                <div class="ai-card">
                    <div class="ai-pre">{{ $parsedFilesPreview }}</div>
                </div>
            </div>
        </div>
    @endif

    <div class="ai-collapse-card">
        <button type="button" class="ai-collapse-toggle" data-ai-collapse-toggle aria-expanded="false">
            <span class="ai-collapse-title">Resposta final integrada</span>
            <span class="ai-collapse-icon"><i class="fa-solid fa-chevron-down"></i></span>
        </button>
        <div class="ai-collapse-body" data-ai-collapse-body hidden>
            <div class="ai-card">
                <div class="ai-pre">{{ $run->final_answer ?: 'Sem resposta final.' }}</div>
            </div>
        </div>
    </div>

    <div class="ai-collapse-card">
        <button type="button" class="ai-collapse-toggle" data-ai-collapse-toggle aria-expanded="false">
            <span class="ai-collapse-title">Respostas por provider</span>
            <span class="ai-collapse-icon"><i class="fa-solid fa-chevron-down"></i></span>
        </button>
        <div class="ai-collapse-body" data-ai-collapse-body hidden>
            <div class="ai-card">
                <div class="ai-grid">
                    @foreach($run->responses as $response)
                        <div class="ai-provider-box">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>{{ strtoupper($response->provider) }}</strong>
                                <span class="ai-badge">{{ $response->status }}</span>
                            </div>
                            <div class="small ai-muted mb-1">Model: {{ $response->model ?: 'n/a' }}</div>
                            <div class="small ai-muted mb-1">Tokens in/out: {{ (int) $response->tokens_in }}/{{ (int) $response->tokens_out }}</div>
                            <div class="small ai-muted mb-3">Custo: ${{ number_format((float) $response->cost_estimate_usd, 4) }}</div>

                            @if($response->error)
                                <div class="alert alert-danger py-2">{{ $response->error }}</div>
                            @endif

                            <div class="ai-pre">{{ $response->raw_output ?: 'Sem conteúdo.' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @include('ai-consensus::Includes._components.modals')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-ai-collapse-toggle]').forEach(function (toggle) {
                toggle.addEventListener('click', function (event) {
                    event.preventDefault();

                    const wrapper = toggle.closest('.ai-collapse-card');
                    if (!wrapper) {
                        return;
                    }

                    const body = wrapper.querySelector('[data-ai-collapse-body]');
                    if (!body) {
                        return;
                    }

                    const expanded = toggle.getAttribute('aria-expanded') === 'true';
                    toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                    body.hidden = expanded;
                });
            });
        });
    </script>
</div>
@endsection
