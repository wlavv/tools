@php
    $panels = $panels ?? [];
@endphp

<div class="catalog-lsg-card">
    <div style="display:flex;justify-content:space-between;gap:1rem;align-items:center;margin-bottom:.85rem;">
        <div>
            <span class="catalog-lsg-eyebrow">Operational panels</span>
            <h3 style="margin:0;">Ações pendentes</h3>
        </div>

        <a href="{{ route('catalog-manager.action-panels.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-table-columns"></i>
            Ver todos
        </a>
    </div>

    <div class="catalog-lsg-panel-grid">
        @foreach($panels as $panel)
            @include('catalogmanager::components.action-panels.panel', ['panel' => $panel])
        @endforeach
    </div>
</div>
