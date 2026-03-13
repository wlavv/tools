<div class="pm-stats">
    <div class="pm-stat"><div>Total projetos</div><strong>{{ $stats['projects'] ?? 0 }}</strong></div>
    <div class="pm-stat"><div>Projetos pai</div><strong>{{ $stats['root_projects'] ?? 0 }}</strong></div>
    <div class="pm-stat"><div>Total tasks</div><strong>{{ $stats['tasks'] ?? 0 }}</strong></div>
    <div class="pm-stat"><div>Tasks concluídas</div><strong>{{ $stats['tasks_done'] ?? 0 }}</strong></div>
    <div class="pm-stat"><a href="{{ route('project_manager.create') }}" class="btn btn-sm btn-outline-success" style="float: right;"> <i class="fa-solid fa-plus" style="font-size: 20px; height: 25px;padding: 5px;"></i> </a></div>
</div>
