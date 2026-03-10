@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div class="ai-consensus-page">
    @include('ai-consensus::Includes.css')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="ai-card">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="ai-badges">
                    <span class="ai-badge">estado: {{ $run->status }}</span>
                    <span class="ai-badge">template: {{ $run->template_key }}</span>
                    <span class="ai-badge">provider final: {{ $run->final_provider ?: 'n/a' }}</span>
                    <span class="ai-badge">modelo final: {{ $run->final_model ?: 'n/a' }}</span>
                    <span class="ai-badge">custo: ${{ number_format((float) $run->total_cost_estimate_usd, 4) }}</span>
                </div>
            </div>
            <div class="ai-actions">
                <a href="{{ route('ai_consensus.index') }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-angle-left" aria-hidden="true"></i></a>
                <a href="{{ route('ai_consensus.edit', $run->id) }}" class="btn btn-sm btn-outline-warning"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                <form method="POST" action="{{ route('ai_consensus.reprocess', $run->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success"><i class="fa fa-cogs" aria-hidden="true"></i></button>
                </form>
            </div>
        </div>
    </div>

    <div class="ai-grid">
        <div class="ai-card">
            <h5>Prompt original</h5>
            <div class="ai-pre">{{ $run->prompt }}</div>
        </div>

        <div class="ai-card">
            <h5>Resumo técnico</h5>
            <div class="small ai-muted">Tokens entrada: {{ (int) $run->total_tokens_in }}</div>
            <div class="small ai-muted">Tokens saída: {{ (int) $run->total_tokens_out }}</div>
            <div class="small ai-muted">Ficheiros: {{ $run->files->count() }}</div>
            <div class="small ai-muted">Respostas: {{ $run->responses->count() }}</div>
        </div>
    </div>

    @if( count( $run->files ) > 0 )
        <div class="ai-card">
            <h5>Ficheiros anexados</h5>
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
    @endif

    @if($parsedFilesPreview)
        <div class="ai-card">
            <h5>Conteúdo extraído dos ficheiros</h5>
            <div class="ai-pre">{{ $parsedFilesPreview }}</div>
        </div>
    @endif

    <div class="ai-card">
        <h5>Resposta final integrada</h5>
        <div class="ai-pre">{{ $run->final_answer ?: 'Sem resposta final.' }}</div>
    </div>

    <div class="ai-card">
        <h5>Respostas por provider</h5>
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

    @include('ai-consensus::Includes._components.modals')
</div>
@endsection

@push('scripts')
    @include('ai-consensus::Includes.js')
@endpush
