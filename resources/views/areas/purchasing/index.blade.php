@extends('layouts.app')

@section('content')
    @include('areas.partials.product-growth-work-panel', ['areaKey' => 'purchasing'])
    @include('areas.partials.idea-panel', ['areaKey' => 'purchasing'])

    <div class="customPanel">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div class="text-muted small fw-bold text-uppercase">Operational area</div>
                <h1 class="h4 mb-1">Purchasing</h1>
                <p class="mb-0 text-muted">Compras, fornecedores, encomendas, rececoes e workflows ERP.</p>
            </div>
            @if(Route::has('erp.dashboard'))
                <a href="{{ route('erp.dashboard') }}" class="btn btn-primary">
                    <i class="fa-solid fa-diagram-project me-2"></i>
                    ERP
                </a>
            @endif
        </div>
    </div>
@endsection
