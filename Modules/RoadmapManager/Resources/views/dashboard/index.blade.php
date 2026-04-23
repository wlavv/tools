@extends('layouts.app')

@section('content')
@include('roadmap-manager::partials.styles')
@include('roadmap-manager::partials.alerts')
<div class="rm-title-row">
    <h2 class="rm-title-row__title">Roadmap Dashboard</h2>
    <div class="lsg-page-actions">
        <a href="{{ route('roadmap_manager.groups.index') }}" class="lsg-action-btn lsg-action-btn--primary"><i class="fa-solid fa-layer-group"></i><span>Groups</span></a>
        <a href="{{ route('roadmap_manager.projects.index') }}" class="lsg-action-btn lsg-action-btn--primary"><i class="fa-solid fa-folder-tree"></i><span>Projects</span></a>
        <a href="{{ route('roadmap_manager.tasks.tree') }}" class="lsg-action-btn lsg-action-btn--primary"><i class="fa-solid fa-sitemap"></i><span>Task Tree</span></a>
    </div>
</div>

<div class="rm-counters">
    @foreach([
        ['Projects', $projectCount, 'fa-folder-tree'],
        ['Groups', $groupCount, 'fa-layer-group'],
        ['Milestones', $milestoneCount, 'fa-flag-checkered'],
        ['Tasks', $taskCount, 'fa-list-check']
    ] as [$label,$value,$icon])
        <div class="rm-kpi">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="rm-kpi__label">{{ $label }}</div>
                    <div class="rm-kpi__value">{{ $value }}</div>
                </div>
                <i class="fa-solid {{ $icon }} fa-xl" style="color:#2563eb"></i>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="rm-panel h-100">
            <div class="rm-title-row" style="margin-bottom:.75rem"><h3 class="rm-title-row__title">Tasks by Status</h3></div>
            <canvas id="statusChart" height="160"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="rm-panel h-100">
            <div class="rm-title-row" style="margin-bottom:.75rem"><h3 class="rm-title-row__title">Projects by Group</h3></div>
            <canvas id="groupChart" height="160"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="rm-panel">
            <div class="rm-title-row" style="margin-bottom:.75rem"><h3 class="rm-title-row__title">Recent Projects</h3></div>
            <div class="rm-table-wrap">
                <table class="rm-table">
                    <thead><tr><th>Name</th><th>Status</th><th>Deadline</th></tr></thead>
                    <tbody>
                    @forelse($recentProjects as $project)
                        <tr>
                            <td><a href="{{ route('roadmap_manager.projects.show', $project->id) }}">{{ $project->name }}</a></td>
                            <td>{{ $project->status }}</td>
                            <td>{{ $project->deadline }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="rm-muted">No projects found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="rm-panel">
            <div class="rm-title-row" style="margin-bottom:.75rem"><h3 class="rm-title-row__title">Upcoming Milestones</h3></div>
            <div class="rm-table-wrap">
                <table class="rm-table">
                    <thead><tr><th>Milestone</th><th>Project</th><th>End</th></tr></thead>
                    <tbody>
                    @forelse($upcomingMilestones as $milestone)
                        <tr>
                            <td><a href="{{ route('roadmap_manager.milestones.show', $milestone->id) }}">{{ $milestone->name }}</a></td>
                            <td>{{ $milestone->project->name ?? '-' }}</td>
                            <td>{{ optional($milestone->planned_end_date)->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="rm-muted">No milestones found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const statusCtx = document.getElementById('statusChart');
if (statusCtx) {
    new Chart(statusCtx, {type:'doughnut',data:{labels:{!! json_encode(array_keys($taskStatus)) !!},datasets:[{data:{!! json_encode(array_values($taskStatus)) !!}}]}});
}
const groupCtx = document.getElementById('groupChart');
if (groupCtx) {
    new Chart(groupCtx, {type:'bar',data:{labels:{!! json_encode($groupProjects->pluck('name')->values()) !!},datasets:[{label:'Projects',data:{!! json_encode($groupProjects->pluck('projects_count')->values()) !!}}]},options:{plugins:{legend:{display:false}}}});
}
</script>
@endsection
