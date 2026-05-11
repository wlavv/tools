@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <div class="dms-workspace-list">
        @forelse($workspaces as $workspace)
            <div class="dms-card dms-workspace-card">
                <div class="dms-workspace-card__icon">
                    <i class="{{ $workspace->icon ?: 'fa-solid fa-layer-group' }}"></i>
                </div>
                <div class="dms-workspace-card__body">
                    <h3>{{ $workspace->name }}</h3>
                    <p>{{ $workspace->description ?: 'Workspace operacional.' }}</p>
                </div>
                <a href="{{ route('document-manager.workspaces.edit', $workspace->id) }}" class="btn btn-outline-warning btn-sm dms-workspace-card__edit">
                    <i class="fa-solid fa-pencil"></i>
                </a>
            </div>
        @empty
            @foreach(['Finance', 'Supplier', 'Legal', 'Product', 'Logistics', 'Compliance', 'AI', 'HR'] as $workspace)
                <div class="dms-card dms-workspace-card">
                    <div class="dms-workspace-card__icon">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div class="dms-workspace-card__body">
                        <h3>{{ $workspace }}</h3>
                        <p>Blueprint pronto para regras, filtros, KPIs e workflows dedicados.</p>
                    </div>
                </div>
            @endforeach
        @endforelse
    </div>
@endsection
