@extends(config('project-manager.layout', 'layouts.app'))

@section('content')
@include('project-manager::Includes.css')
@include('project-manager::Includes.js')

<div class="pm-page">
    @include('project-manager::Includes._components.header', ['title' => $project->name, 'subtitle' => $project->status])

    <div class="pm-top-actions">
        <a href="{{ route('project_manager.edit', $project) }}" class="btn btn-warning">Editar</a>
        <a href="{{ route('project_manager.index') }}" class="btn btn-secondary">Voltar</a>
        <form method="POST" action="{{ route('project_manager.destroy', $project) }}" onsubmit="return confirm('Remover projeto?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Apagar</button>
        </form>
    </div>

    <div class="pm-card">
        <div class="pm-tabs">
            <button type="button" class="pm-tab is-active" data-tab="overview">Overview</button>
            <button type="button" class="pm-tab" data-tab="branding">Branding</button>
            <button type="button" class="pm-tab" data-tab="contacts">Contacts</button>
            <button type="button" class="pm-tab" data-tab="team">Team</button>
            <button type="button" class="pm-tab" data-tab="documentation">Documentation</button>
            <button type="button" class="pm-tab" data-tab="tasks">Tasks</button>
        </div>

        <div class="pm-tab-panel is-active" data-panel="overview">
            <div class="pm-grid pm-grid-2">
                <div class="pm-info-box">
                    <h4>Dados principais</h4>
                    <p><strong>Slug:</strong> {{ $project->slug }}</p>
                    <p><strong>Status:</strong> {{ $project->status }}</p>
                    <p><strong>Progresso:</strong> {{ $project->progress_percent }}%</p>
                    <p><strong>Início:</strong> {{ optional($project->start_date)->format('Y-m-d') }}</p>
                    <p><strong>Deadline:</strong> {{ optional($project->deadline)->format('Y-m-d') }}</p>
                    <p><strong>URL:</strong> {{ $project->url }}</p>
                </div>
                <div class="pm-info-box">
                    <h4>Resumo</h4>
                    <p>{{ $project->description ?: 'Sem descrição.' }}</p>
                    <div class="pm-progress">
                        <div class="pm-progress-bar" style="width: {{ $project->progress_percent }}%;"></div>
                    </div>
                    <small>{{ $project->tasks_done_count }}/{{ $project->tasks_total_count }} tasks concluídas</small>
                </div>
            </div>
        </div>

        <div class="pm-tab-panel" data-panel="branding">
            <div class="pm-grid pm-grid-3">
                <div class="pm-info-box">
                    <h4>Cores</h4>
                    <p><strong>Primary:</strong> {{ $project->primary_color }}</p>
                    <p><strong>Secondary:</strong> {{ $project->secondary_color }}</p>
                    <p><strong>Accent:</strong> {{ $project->accent_color }}</p>
                </div>
                <div class="pm-info-box">
                    <h4>Tipografia</h4>
                    <p><strong>Fonte:</strong> {{ $project->font_family }}</p>
                </div>
                <div class="pm-info-box">
                    <h4>Notas</h4>
                    <p>{{ $project->brand_notes ?: 'Sem notas.' }}</p>
                </div>
            </div>
        </div>

        <div class="pm-tab-panel" data-panel="contacts">
            <div class="pm-grid pm-grid-2">
                <div class="pm-info-box">
                    <h4>Contacto principal</h4>
                    <p><strong>Nome:</strong> {{ $project->contact_name }}</p>
                    <p><strong>Email:</strong> {{ $project->contact_email }}</p>
                    <p><strong>Telefone:</strong> {{ $project->contact_phone }}</p>
                    <p><strong>Website:</strong> {{ $project->website }}</p>
                </div>
                <div class="pm-info-box">
                    <h4>Redes sociais</h4>
                    <p><strong>Facebook:</strong> {{ $project->social_facebook }}</p>
                    <p><strong>Instagram:</strong> {{ $project->social_instagram }}</p>
                    <p><strong>LinkedIn:</strong> {{ $project->social_linkedin }}</p>
                    <p><strong>YouTube:</strong> {{ $project->social_youtube }}</p>
                </div>
            </div>
        </div>

        <div class="pm-tab-panel" data-panel="team">
            <div class="pm-grid pm-grid-2">
                <div class="pm-info-box">
                    <h4>Equipa</h4>
                    <pre class="pm-pre">{{ $project->team_json ?: '[]' }}</pre>
                </div>
                <div class="pm-info-box">
                    <h4>Notas internas</h4>
                    <p>{{ $project->team_notes ?: 'Sem notas.' }}</p>
                    <h4 class="mt-3">Estrutura</h4>
                    <p>{{ $project->structure_notes ?: 'Sem notas.' }}</p>
                </div>
            </div>
        </div>

        <div class="pm-tab-panel" data-panel="documentation">
            <div class="pm-grid pm-grid-2">
                <div class="pm-info-box">
                    <h4>Links</h4>
                    <p><strong>Repository:</strong> {{ $project->repository_url }}</p>
                    <p><strong>Documentation:</strong> {{ $project->documentation_url }}</p>
                </div>
                <div class="pm-info-box">
                    <h4>Notas</h4>
                    <p>{{ $project->documentation_notes ?: 'Sem notas.' }}</p>
                </div>
            </div>
        </div>

        <div class="pm-tab-panel" data-panel="tasks">
            <div class="pm-grid pm-grid-2">
                <div class="pm-info-box">
                    <h4>Nova task</h4>
                    <form method="POST" action="{{ route('project_manager.tasks.store', $project) }}">
                        @csrf
                        <div class="mb-3">
                            <label>Título</label>
                            <input class="form-control" type="text" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select class="form-control" name="status">
                                @foreach($taskStatuses as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Parent task</label>
                            <select class="form-control" name="id_parent">
                                <option value="0">Root</option>
                                @foreach($project->tasks as $taskOption)
                                    <option value="{{ $taskOption->id }}">{{ $taskOption->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Prioridade</label>
                            <input class="form-control" type="number" name="priority" value="0" min="0">
                        </div>
                        <div class="mb-3">
                            <label>Data início</label>
                            <input class="form-control" type="date" name="start_date">
                        </div>
                        <div class="mb-3">
                            <label>Tempo estimado (dias)</label>
                            <input class="form-control" type="number" name="expected_time" min="0">
                        </div>
                        <div class="mb-3">
                            <label>Comentário</label>
                            <textarea class="form-control" name="comment" rows="4"></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Guardar task</button>
                    </form>
                </div>

                <div class="pm-info-box">
                    <h4>Árvore de tasks</h4>
                    <div class="pm-task-tree">
                        @forelse($project->rootTasks as $task)
                            @include('project-manager::Includes._components.task-item', ['task' => $task, 'project' => $project, 'taskStatuses' => $taskStatuses, 'level' => 0])
                        @empty
                            <p>Sem tasks ainda.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
