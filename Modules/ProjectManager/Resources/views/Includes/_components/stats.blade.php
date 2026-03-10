<div class="pm-stats">
    <div class="pm-stat">
        <div>Total projetos</div>
        <strong>{{ $stats['projects'] ?? 0 }}</strong>
    </div>
    <div class="pm-stat">
        <div>Projetos abertos</div>
        <strong>{{ $stats['open_projects'] ?? 0 }}</strong>
    </div>
    <div class="pm-stat">
        <div>Total tasks</div>
        <strong>{{ $stats['tasks'] ?? 0 }}</strong>
    </div>
    <div class="pm-stat">
        <div>Tasks concluídas</div>
        <strong>{{ $stats['tasks_done'] ?? 0 }}</strong>
    </div>
</div>
