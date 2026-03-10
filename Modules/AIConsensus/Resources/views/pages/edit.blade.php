@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div class="ai-consensus-page">
    @include('ai-consensus::Includes.css')

    @if($errors->any())
        <div class="alert alert-danger">
            <div><strong>Existem erros no formulário.</strong></div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('ai-consensus::Includes._components.form', [
        'action' => route('ai_consensus.update', $run->id),
        'method' => 'PUT',
        'submitLabel' => 'Guardar alterações',
    ])

    <div class="ai-card">
        <h5 class="mb-3">Ficheiros atuais</h5>
        <div class="ai-files-list">
            @forelse($run->files as $file)
                <div class="ai-provider-box">
                    <strong>{{ $file->original_name }}</strong>
                    <div class="small ai-muted">MIME: {{ $file->mime_type ?: 'n/a' }}</div>
                    <div class="small ai-muted">Tamanho: {{ number_format(((int) $file->size_bytes) / 1024, 2) }} KB</div>
                </div>
            @empty
                <div class="ai-muted">Sem ficheiros associados.</div>
            @endforelse
        </div>
    </div>

    @include('ai-consensus::Includes._components.modals')
</div>
@endsection

@push('scripts')
    @include('ai-consensus::Includes.js')
@endpush
