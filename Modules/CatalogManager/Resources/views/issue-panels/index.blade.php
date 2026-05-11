@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Catalog Manager</span>
            <h1>Painéis operacionais</h1>
        </div>
    </div>

    @include('catalogmanager::components.issue-panels.grid', ['panels' => $panels])
@endsection
