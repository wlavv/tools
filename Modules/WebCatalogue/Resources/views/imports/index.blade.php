@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="wc-alert wc-alert-warning"><strong>Validation error.</strong> {{ $errors->first() }}</div>@endif

<div class="wc-hero-card wc-import-hero">
    <div>
        <div class="wc-eyebrow"><i class="fa-solid fa-file-csv"></i> CSV Import Center</div>
        <h2>Importação guiada, validada e por etapas</h2>
        <p>Seleciona o tipo de importação, escolhe a store quando aplicável, descarrega o template, faz upload, valida em preview e só depois confirma a gravação.</p>
    </div>
</div>

<div class="wc-import-flow">
    <section class="wc-import-section is-active" id="wcStepType">
        <div class="wc-import-step"><span>1</span><div><h3>Selecionar o que vais importar</h3><p class="wc-muted">Cada tipo tem um CSV próprio, com colunas obrigatórias e exemplo.</p></div></div>
        <div class="wc-form-grid" style="margin-top:14px">
            <div class="wc-field">
                <label>Tipo de importação</label>
                <select id="wcImportTypeSelect">
                    <option value="">Selecionar...</option>
                    @foreach($templates as $type => $template)
                        <option value="{{ $type }}">{{ $template['label'] ?? ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="wc-field" id="wcStoreSelectWrap" style="display:none">
                <label>Store</label>
                <select id="wcImportStoreSelect" name="id_store">
                    <option value="">Selecionar store...</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }} @if($store->code) ({{ $store->code }}) @endif</option>
                    @endforeach
                </select>
                <div class="wc-muted" style="margin-top:6px">Para importar Stores, esta seleção não é necessária.</div>
            </div>
        </div>
    </section>

    <section class="wc-import-section" id="wcStepTemplate">
        <div class="wc-import-step"><span>2</span><div><h3>Descarregar template CSV</h3><p class="wc-muted">Preenche o ficheiro mantendo os nomes das colunas.</p></div></div>
        @foreach($templates as $type => $template)
            <div class="wc-import-type-panel" data-import-panel="{{ $type }}" style="display:none">
                <div class="wc-template-box">
                    <div class="wc-template-header">
                        <div>
                            <strong><i class="{{ $template['icon'] ?? 'fa-solid fa-file-csv' }}"></i> {{ $template['label'] ?? ucfirst($type) }}</strong>
                            <p class="wc-muted" style="margin:6px 0 0">{{ $template['description'] ?? '' }}</p>
                        </div>
                        <a class="wc-primary-btn wc-template-download" data-type="{{ $type }}" href="{{ route('webcatalogue.imports.template', $type) }}"><i class="fa-solid fa-download"></i> Descarregar template</a>
                    </div>
                    <div class="wc-template-cols">
                        @foreach(($template['columns'] ?? []) as $column)
                            <code class="{{ in_array($column, $template['required'] ?? []) ? 'wc-required-col' : '' }}">{{ $column }}</code>
                        @endforeach
                    </div>
                    @if(!empty($template['sample']))
                        <div class="wc-template-sample">
                            <strong>Exemplo</strong>
                            <div class="wc-sample-scroll">
                                <table class="wc-mini-table"><thead><tr>
                                    @foreach(($template['columns'] ?? []) as $column)<th>{{ $column }}</th>@endforeach
                                </tr></thead><tbody><tr>
                                    @foreach(($template['sample'] ?? []) as $value)<td>{{ $value }}</td>@endforeach
                                </tr></tbody></table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </section>

    <section class="wc-import-section" id="wcStepUpload">
        <div class="wc-import-step"><span>3</span><div><h3>Upload do CSV preenchido</h3><p class="wc-muted">Depois do upload vais para a validação pré-save.</p></div></div>
        @foreach($templates as $type => $template)
            <form class="wc-dropzone-form" data-upload-form="{{ $type }}" method="POST" action="{{ route('webcatalogue.imports.upload', $type) }}" enctype="multipart/form-data" style="display:none">
                @csrf
                <input type="hidden" name="id_store" data-hidden-store>
                <label class="wc-dropzone">
                    <input type="file" name="csv_file" accept=".csv,.txt" required>
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <strong>Arrasta o CSV para aqui ou clica para escolher</strong>
                    <span>Formato aceite: .csv ou .txt</span>
                </label>
                <div class="wc-actions-row" style="margin-top:14px">
                    <button class="wc-primary-btn" type="submit"><i class="fa-solid fa-magnifying-glass-chart"></i> Upload e validar preview</button>
                </div>
            </form>
        @endforeach
    </section>

    <section class="wc-import-section wc-import-section-muted" id="wcStepPreviewHint">
        <div class="wc-import-step"><span>4</span><div><h3>Validação pré-save</h3><p class="wc-muted">Esta secção abre automaticamente depois do upload, com as linhas válidas/inválidas e botão de confirmação.</p></div></div>
    </section>
</div>

<div class="wc-card" style="margin-top:16px">
    <h3>Recent import batches</h3>
    <table class="wc-table">
        <thead><tr><th>ID</th><th>Type</th><th>File</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        @forelse($batches as $batch)
            <tr>
                <td><a href="{{ route('webcatalogue.imports.preview', $batch) }}">#{{ $batch->id }}</a></td>
                <td>{{ $batch->source_type }}</td><td>{{ $batch->filename }}</td><td><span class="wc-badge">{{ $batch->status }}</span></td><td>{{ $batch->created_at }}</td>
            </tr>
        @empty
            <tr><td colspan="5">No imports yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var select = document.getElementById('wcImportTypeSelect');
    var storeWrap = document.getElementById('wcStoreSelectWrap');
    var storeSelect = document.getElementById('wcImportStoreSelect');
    var templateStep = document.getElementById('wcStepTemplate');
    var uploadStep = document.getElementById('wcStepUpload');
    var panels = document.querySelectorAll('[data-import-panel]');
    var forms = document.querySelectorAll('[data-upload-form]');
    var hiddenStores = document.querySelectorAll('[data-hidden-store]');
    var downloaded = {};

    function selectedTypeRequiresStore() {
        return select && select.value && select.value !== 'stores';
    }
    function refresh() {
        var type = select ? select.value : '';
        var storeOk = !selectedTypeRequiresStore() || (storeSelect && storeSelect.value);
        if (storeWrap) storeWrap.style.display = type && type !== 'stores' ? 'block' : 'none';
        panels.forEach(function (panel) { panel.style.display = panel.getAttribute('data-import-panel') === type && storeOk ? 'block' : 'none'; });
        forms.forEach(function (form) { form.style.display = form.getAttribute('data-upload-form') === type && storeOk && downloaded[type] ? 'block' : 'none'; });
        hiddenStores.forEach(function (input) { input.value = storeSelect ? storeSelect.value : ''; });
        templateStep.classList.toggle('is-active', !!type && storeOk);
        uploadStep.classList.toggle('is-active', !!type && storeOk && !!downloaded[type]);
    }
    document.querySelectorAll('.wc-template-download').forEach(function (link) {
        link.addEventListener('click', function () {
            downloaded[link.getAttribute('data-type')] = true;
            setTimeout(refresh, 150);
        });
    });
    if (select) select.addEventListener('change', refresh);
    if (storeSelect) storeSelect.addEventListener('change', refresh);
    refresh();
});
</script>
@endsection
