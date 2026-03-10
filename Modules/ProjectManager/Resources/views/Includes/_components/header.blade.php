<div class="pm-header">
    <div class="pm-title">
        <h1>{{ $title ?? 'Project Manager' }}</h1>
        @if(!empty($subtitle))
            <div class="pm-subtitle">{{ $subtitle }}</div>
        @endif
    </div>
    <div>
        <a href="{{ route('project_manager.create') }}" class="btn btn-primary">Novo projeto</a>
    </div>
</div>
