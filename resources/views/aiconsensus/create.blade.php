@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel">
                <div class="container">
                    <h2 class="mb-3">New AI Consensus Run</h2>
                    <form method="POST" action="{{ route('aiconsensus.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Title (optional)</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Template</label>
                            <select name="template_key" class="form-select">
                                @foreach ($templates as $key => $t)
                                    <option value="{{ $key }}"
                                        {{ old('template_key', 'module_scaffold_v1') === $key ? 'selected' : '' }}>
                                        {{ $t['label'] ?? $key }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Draft providers: {{ implode(', ', $draftProviders) }} → Integrator:
                                openai</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prompt</label>
                            <textarea name="prompt" rows="10" class="form-control" required>{{ old('prompt') }}</textarea>
                            <div class="form-text">Escreve o pedido como uma especificação técnica curta.</div>
                        </div>
                        <button class="btn btn-primary">Run</button>
                        <a class="btn btn-link" href="{{ route('aiconsensus.index') }}">Back</a>
                    </form>
                </div>

            </div>

        </div>

    </div>
@endsection
