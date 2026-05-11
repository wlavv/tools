@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">LSG Operational Catalog</span>
            <h1>Catalog Manager</h1>
            <p>Entry point operacional do modulo. Usa o dashboard para uma leitura completa do estado do catalogo.</p>
        </div>

        <div class="catalog-lsg-actions">
            <a href="{{ route('catalog-manager.dashboard') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>
            <a href="{{ route('catalog-manager.diagnostics.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-stethoscope"></i> Diagnostico
            </a>
        </div>
    </div>
@endsection
