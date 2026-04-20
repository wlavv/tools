@extends(config('productivitymanager.layout', 'layouts.app'))

@section('content')
<div>
    @include('productivitymanager::Includes.css')

    <div class="productivity-manager-shell">
        <div class="productivity-counters">
            <div class="productivityManager-card productivity-counter">
                <div class="productivity-counter__label">Today's Tasks</div>
                <div class="productivity-counter__value">{{ count($dashboard['today']) }}</div>
            </div>
            <div class="productivityManager-card productivity-counter">
                <div class="productivity-counter__label">Blocked</div>
                <div class="productivity-counter__value">{{ count($dashboard['blocked']) }}</div>
            </div>
            <div class="productivityManager-card productivity-counter">
                <div class="productivity-counter__label">Projects</div>
                <div class="productivity-counter__value">{{ count($dashboard['projects']) }}</div>
            </div>
            <div class="productivityManager-card productivity-counter">
                <div class="productivity-counter__label">Alerts</div>
                <div class="productivity-counter__value">{{ count($dashboard['alerts']) }}</div>
            </div>
        </div>

        <div class="productivity-dashboard-grid">
            <div class="productivity-manager-card">
                <h2 class="productivity-card-title">Top Tasks</h2>
                <div class="productivity-list">
                    @forelse($dashboard['today'] as $task)
                        <div class="productivity-item">
                            <div>
                                <p class="productivity-item__title">{{ $task->title }}</p>
                                <div class="productivity-item__meta">{{ $task->project }} • {{ strtoupper($task->priority) }}</div>
                            </div>
                            <span class="productivity-badge productivity-badge--neutral">{{ $task->status }}</span>
                        </div>
                    @empty
                        <div class="productivity-empty-state">
                            <strong>No tasks found.</strong>
                            <span>There are no active tasks to display right now.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="productivity-manager-card">
                <h2 class="productivity-card-title">Blocked</h2>
                <div class="productivity-list">
                    @forelse($dashboard['blocked'] as $task)
                        <div class="productivity-item">
                            <div>
                                <p class="productivity-item__title">{{ $task->title }}</p>
                                <div class="productivity-item__meta">
                                    {{ $task->project }}
                                    @if(!empty($task->blocked_by)) • {{ $task->blocked_by }} @endif
                                </div>
                                @if(!empty($task->blocked_reason))
                                    <div class="productivity-item__note">{{ $task->blocked_reason }}</div>
                                @endif
                            </div>
                            <span class="productivity-badge productivity-badge--warning">Blocked</span>
                        </div>
                    @empty
                        <div class="productivity-empty-state">
                            <strong>No blocked tasks.</strong>
                            <span>Great — nothing is currently blocked.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="productivity-grid">
            <div class="productivity-manager-card">
                <h2 class="productivity-card-title">Project Progress</h2>
                <div class="productivity-list">
                    @forelse($dashboard['projects'] as $project)
                        <div class="productivity-item">
                            <div style="width:100%;">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>{{ $project->project }}</span>
                                    <span>{{ $project->progress }}%</span>
                                </div>
                                <div class="productivity-progress">
                                    <div class="productivity-progress__bar" style="width: {{ $project->progress }}%"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="productivity-empty-state">
                            <strong>No projects found.</strong>
                            <span>Project progress will appear here once tasks exist.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="productivity-manager-card">
                <h2 class="productivity-card-title">Alerts</h2>
                <div class="productivity-list">
                    @forelse($dashboard['alerts'] as $alert)
                        <div class="productivity-item">
                            <div>
                                <p class="productivity-item__title">{{ $alert->title }}</p>
                                <div class="productivity-item__meta">{{ $alert->source }} • {{ optional($alert->created_at)->format ? $alert->created_at->format('Y-m-d H:i') : $alert->created_at }}</div>
                            </div>
                            <span class="productivity-badge productivity-badge--{{ in_array($alert->severity, ['critical', 'high']) ? 'warning' : 'neutral' }}">{{ strtoupper($alert->severity) }}</span>
                        </div>
                    @empty
                        <div class="productivity-empty-state">
                            <strong>No alerts.</strong>
                            <span>No active alerts are currently registered.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
