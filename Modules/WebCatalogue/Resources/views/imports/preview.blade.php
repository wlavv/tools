@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-hero-card">
    <div>
        <div class="wc-eyebrow"><i class="fa-solid fa-clipboard-check"></i> Import Preview</div>
        <h2>{{ $template['label'] ?? ucfirst($type) }} · Batch #{{ $batch->id }}</h2>
        <p>Valida as linhas antes de gravar. A gravação final cria/atualiza as tabelas reais; o payload é apenas auditoria da importação.</p>
    </div>
    <div class="wc-hero-actions">
        @if(in_array($batch->status, ['preview_ready', 'preview_with_errors'], true))
        <form id="lsg-form" method="POST" action="{{ route('webcatalogue.imports.confirm', $batch) }}">
            @csrf
            <button class="wc-primary-btn" type="submit"><i class="fa-solid fa-check"></i> Confirmar importação</button>
        </form>
        @else
            <span class="wc-badge">{{ $batch->status }}</span>
        @endif
    </div>
</div>

<div class="wc-grid wc-kpi-grid">
    <div class="wc-kpi-card wc-kpi-card-product"><i class="fa-solid fa-table-list wc-kpi-bg-icon"></i><div class="wc-kpi-content"><h3>Total rows</h3><div class="wc-kpi">{{ $batch->total_rows }}</div></div></div>
    <div class="wc-kpi-card wc-kpi-card-store"><i class="fa-solid fa-check wc-kpi-bg-icon"></i><div class="wc-kpi-content"><h3>Valid</h3><div class="wc-kpi">{{ $batch->rows()->where('status','valid')->count() }}</div></div></div>
    <div class="wc-kpi-card wc-kpi-card-catalogue"><i class="fa-solid fa-triangle-exclamation wc-kpi-bg-icon"></i><div class="wc-kpi-content"><h3>Invalid</h3><div class="wc-kpi">{{ $batch->rows()->where('status','invalid')->count() }}</div></div></div>
</div>

@php
    $columns = $template['columns'] ?? [];
@endphp

<div class="wc-card" style="margin-top:16px">
    <div class="wc-section-head"><div><h3>Pre-save validation</h3><p class="wc-muted">A pré-visualização usa as colunas reais do template. Os dados só são gravados nas tabelas finais após confirmação.</p></div><span class="wc-badge">{{ $batch->status }}</span></div>
    <div class="wc-sample-scroll">
        <table class="wc-table lsg-datatable">
            <thead>
                <tr>
                    <th>Row</th>
                    <th>Status</th>
                    <th>Message</th>
                    @foreach($columns as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach($rows as $row)
                @php $payload = is_array($row->raw_payload) ? $row->raw_payload : (json_decode($row->raw_payload ?? '[]', true) ?: []); @endphp
                <tr>
                    <td>{{ $row->row_number }}</td>
                    <td><span class="wc-badge wc-badge-{{ $row->status }}">{{ $row->status }}</span></td>
                    <td>{{ $row->message }}</td>
                    @foreach($columns as $column)
                        <td>{{ \Illuminate\Support\Str::limit((string)($payload[$column] ?? ''), 80) }}</td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="wc-pagination">{{ $rows->links() }}</div>
</div>
</div>
@endsection
