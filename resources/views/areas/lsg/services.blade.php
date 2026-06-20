@extends('layouts.app')

@push('styles')
    @include('areas.lsg.partials.section-cards-css')
@endpush

@section('content')
    <div class="lsg-section-panel">
        <div class="lsg-section-head">
            <div>
                <span>LSG</span>
                <h3>Servicos</h3>
            </div>
        </div>

        <div class="lsg-section-grid">
            <section class="lsg-section-card">
                <div class="lsg-section-card__head"><i class="fa-solid fa-truck-fast"></i><strong>Tracking</strong></div>
                <span>Envios, trackings e clientes tracking.</span>
                <div class="lsg-section-actions">
                    @if(Route::has('package_tracker.dashboard'))<a class="btn btn-sm btn-outline-primary" href="{{ route('package_tracker.dashboard') }}">Dashboard</a>@endif
                    @if(Route::has('package_tracker.shipments.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('package_tracker.shipments.index') }}">Trackings</a>@endif
                    @if(Route::has('package_tracker.clients.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('package_tracker.clients.index') }}">Clientes</a>@endif
                </div>
            </section>

            <section class="lsg-section-card">
                <div class="lsg-section-card__head"><i class="fa-solid fa-folder-tree"></i><strong>Document Manager</strong></div>
                <span>Documentos e ficheiros operacionais do grupo.</span>
                <div class="lsg-section-actions">
                    @if(Route::has('document-manager.dashboard'))<a class="btn btn-sm btn-outline-primary" href="{{ route('document-manager.dashboard') }}">Abrir</a>@endif
                </div>
            </section>

            <section class="lsg-section-card">
                <div class="lsg-section-card__head"><i class="fa-solid fa-book-open"></i><strong>WebCatalogue</strong></div>
                <span>Catalogos digitais, sessoes e publicacao visual.</span>
                <div class="lsg-section-actions">
                    @if(Route::has('webcatalogue.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('webcatalogue.index') }}">Abrir</a>@endif
                </div>
            </section>

            <section class="lsg-section-card">
                <div class="lsg-section-card__head"><i class="fa-solid fa-file-import"></i><strong>Data Tools</strong></div>
                <span>Importacao e exportacao operacional.</span>
                <div class="lsg-section-actions">
                    @if(Route::has('data_import_wizard.dashboard'))<a class="btn btn-sm btn-outline-primary" href="{{ route('data_import_wizard.dashboard') }}">Import</a>@endif
                    @if(Route::has('data_export_center.dashboard'))<a class="btn btn-sm btn-outline-primary" href="{{ route('data_export_center.dashboard') }}">Export</a>@endif
                </div>
            </section>
        </div>
    </div>
@endsection
