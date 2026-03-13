@php
$statusClass = match(strtolower($project->status ?? '')) {
    'in progress' => 'pm-status-in-progress',
    'new' => 'pm-status-new',
    'hold' => 'pm-status-hold',
    'done' => 'pm-status-done',
    default => 'pm-status-default',
};
@endphp
<div class="pm-project-card">
    <div class="pm-project-inner">
        <div class="pm-project-title">{{ $project->name }}</div>
        <div class="pm-project-logo-wrap">
            <div class="pm-project-logo">
                @if(!empty($project->logo))
                    <img src="{{ $project->logo }}" alt="{{ $project->name }}">
                @else
                    <span>{{ $project->name }}</span>
                @endif
            </div>
        </div>
        <div class="pm-project-actions">
            <a href="{{ route('project_manager.create') }}?id_parent={{ $project->id }}" class="pm-btn-mini pm-btn-success" title="New item"><i class="fa-solid fa-plus" style="font-size: 20px; height: 25px;padding: 5px;"></i></a>
            <a href="{{ route('project_manager.show', $project) }}" class="pm-btn-mini pm-btn-info" title="Show item"><i class="fa-solid fa-eye"></i></a>
            <a href="{{ route('project_manager.edit', $project) }}" class="pm-btn-mini pm-btn-warn" title="Edit item"><i class="fa-solid fa-pencil"></i></a>
            <form method="POST" action="{{ route('project_manager.destroy', $project) }}" onsubmit="return confirm('Apagar projeto?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="pm-btn-mini pm-btn-danger" title="Delete item"><i class="fa-solid fa-trash"></i></button>
            </form>
        </div>
    </div>
    <div class="pm-status-bar {{ $statusClass }}">{{ $project->status ?: 'No status' }}</div>
</div>
