@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Roadmap Dashboard</h1>
    <div class="btn-group">
        <a href="{{ route('roadmap.groups.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-layer-group"></i> Groups</a>
        <a href="{{ route('roadmap.projects.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-folder-tree"></i> Projects</a>
        <a href="{{ route('roadmap.tasks.tree') }}" class="btn btn-outline-primary"><i class="fa-solid fa-sitemap"></i> Task Tree</a>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['Projects', $projectCount, 'fa-folder-tree'],
        ['Groups', $groupCount, 'fa-layer-group'],
        ['Milestones', $milestoneCount, 'fa-flag-checkered'],
        ['Tasks', $taskCount, 'fa-list-check']
    ] as [$label,$value,$icon])
    <div class="col-md-3">
        <div class="rm-panel">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="h3 mb-0">{{ $value }}</div>
                </div>
                <i class="fa-solid {{ $icon }} fa-2x text-primary"></i>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header">Tasks by Status</div>
            <div class="rm-panel-body">
                <canvas id="statusChart" height="160"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header">Projects by Group</div>
            <div class="rm-panel-body">
                <canvas id="groupChart" height="160"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="rm-panel">
            <div class="card-header">Recent Projects</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Name</th><th>Status</th><th>Deadline</th></tr></thead>
                    <tbody>
                    @forelse($recentProjects as $project)
                        <tr>
                            <td><a href="{{ route('roadmap.projects.show', $project->id) }}">{{ $project->name }}</a></td>
                            <td>{{ $project->status }}</td>
                            <td>{{ $project->deadline }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">No projects found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="rm-panel">
            <div class="card-header">Upcoming Milestones</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Milestone</th><th>Project</th><th>End</th></tr></thead>
                    <tbody>
                    @forelse($upcomingMilestones as $milestone)
                        <tr>
                            <td><a href="{{ route('roadmap.milestones.show', $milestone->id) }}">{{ $milestone->name }}</a></td>
                            <td>{{ $milestone->project->name ?? '-' }}</td>
                            <td>{{ optional($milestone->planned_end_date)->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">No milestones found.</td></tr>
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
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($taskStatus)) !!},
            datasets: [{ data: {!! json_encode(array_values($taskStatus)) !!} }]
        }
    });
}
const groupCtx = document.getElementById('groupChart');
if (groupCtx) {
    new Chart(groupCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($groupProjects->pluck('name')->values()) !!},
            datasets: [{ label: 'Projects', data: {!! json_encode($groupProjects->pluck('projects_count')->values()) !!} }]
        },
        options: { plugins: { legend: { display: false } } }
    });
}
</script>
@endsection
