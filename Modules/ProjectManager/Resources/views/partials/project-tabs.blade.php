@php
    use Modules\ProjectManager\Services\ProjectManagerSectionRegistry;
    $activeTab = $activeTab ?? 'overview';
    $tabs = [
        'overview' => ['label' => 'Overview', 'hint' => 'Execução atual', 'icon' => 'fa-solid fa-gauge-high', 'route' => route('project_manager.projects.show', $project->id)],
        'tasks' => ['label' => 'Tasks', 'hint' => 'Árvore e estados', 'icon' => 'fa-solid fa-list-check', 'route' => route('project_manager.projects.tasks.index', $project->id)],
        'roadmap' => ['label' => 'Roadmap', 'hint' => 'Timeline dinâmica', 'icon' => 'fa-solid fa-route', 'route' => route('project_manager.projects.roadmap.index', $project->id)],
        'productivity' => ['label' => 'Productivity', 'hint' => 'Kanban / foco', 'icon' => 'fa-solid fa-bolt', 'route' => route('project_manager.projects.productivity', $project->id)],
        'details' => ['label' => 'Project Details', 'hint' => 'Design / stack / assets', 'icon' => 'fa-solid fa-sliders', 'route' => route('project_manager.projects.details', $project->id)],
    ];
@endphp

<div class="pm-tabs-wrap pm-tabs-wrap--wc">
    <div class="pm-tabs">
        @foreach($tabs as $key => $tab)
            <a class="pm-tab {{ $activeTab === $key ? 'is-active' : '' }}" href="{{ $tab['route'] }}">
                <i class="{{ $tab['icon'] }}"></i>
                <span>
                    <strong>{{ $tab['label'] }}</strong>
                    <small>{{ $tab['hint'] }}</small>
                </span>
            </a>
        @endforeach
    </div>
</div>

<style>
.pm-tabs-wrap--wc .pm-tab{min-width:150px;align-items:flex-start;}
.pm-tabs-wrap--wc .pm-tab span{display:flex;flex-direction:column;line-height:1.12;}
.pm-tabs-wrap--wc .pm-tab strong{font-size:12px;}
.pm-tabs-wrap--wc .pm-tab small{font-size:10px;font-weight:800;opacity:.62;margin-top:2px;}
.pm-tabs-wrap--wc .pm-tab.is-active small{color:rgba(255,255,255,.64);opacity:1;}
</style>
