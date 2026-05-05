@extends(config('project-manager.layout', 'layouts.app'))

@section('content')
@include('project-manager::partials.styles')

<div class="container-fluid pm-wrap">
    <div class="pm-shell">
        @if($errors->any())
            <div class="alert alert-danger mb-0">
                <strong>Verifica os dados.</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="lsg-form" class="pm-form" method="POST" action="{{ $project->exists ? route('project_manager.projects.update', $project->id) : route('project_manager.projects.store') }}">
            @csrf
            @if($project->exists) @method('PUT') @endif

            <div class="pm-grid pm-grid-2">
                <div class="pm-card">
                    <div class="pm-card-title"><i class="fa-solid fa-diagram-project"></i> Base</div>
                    <div class="pm-card-subtitle">Dados essenciais do projeto.</div>
                    <div class="row g-3">
                        <div class="col-md-8"><label>Nome</label><input required name="name" class="form-control" value="{{ old('name', $project->name) }}"></div>
                        <div class="col-md-4"><label>Slug</label><input name="slug" class="form-control" value="{{ old('slug', $project->slug) }}"></div>
                        <div class="col-md-4"><label>Código</label><input name="code" class="form-control" value="{{ old('code', $project->code) }}"></div>
                        <div class="col-md-4"><label>Tipo</label><input name="project_type" class="form-control" value="{{ old('project_type', $project->project_type ?? 'software') }}"></div>
                        <div class="col-md-4"><label>Status</label><input name="status" class="form-control" value="{{ old('status', $project->status ?? 'active') }}"></div>
                        <div class="col-md-4"><label>Prioridade</label><input type="number" name="priority" class="form-control" value="{{ old('priority', $project->priority) }}"></div>
                        <div class="col-md-4"><label>Progresso %</label><input type="number" step="0.01" name="progress_percent" class="form-control" value="{{ old('progress_percent', $project->progress_percent) }}"></div>
                        <div class="col-md-4"><label>Health</label><input name="health_status" class="form-control" value="{{ old('health_status', $project->health_status ?? 'normal') }}"></div>
                        <div class="col-12"><label>Descrição</label><textarea name="description" class="form-control" rows="4">{{ old('description', $project->description) }}</textarea></div>
                    </div>
                </div>

                <div class="pm-card">
                    <div class="pm-card-title"><i class="fa-solid fa-link"></i> Links e execução</div>
                    <div class="pm-card-subtitle">URLs principais e orientação operacional.</div>
                    <div class="row g-3">
                        <div class="col-md-6"><label>Foco atual</label><input name="current_focus" class="form-control" value="{{ old('current_focus', $project->current_focus) }}"></div>
                        <div class="col-md-6"><label>Próximo passo</label><input name="next_step" class="form-control" value="{{ old('next_step', $project->next_step) }}"></div>
                        <div class="col-md-6"><label>Start date</label><input type="date" name="start_date" class="form-control" value="{{ old('start_date', $project->start_date ? substr((string) $project->start_date, 0, 10) : null) }}"></div>
                        <div class="col-md-6"><label>Deadline</label><input type="date" name="deadline" class="form-control" value="{{ old('deadline', $project->deadline ? substr((string) $project->deadline, 0, 10) : null) }}"></div>
                        <div class="col-12"><label>Website</label><input name="website" class="form-control" value="{{ old('website', $project->website) }}"></div>
                        <div class="col-12"><label>Repository URL</label><input name="repository_url" class="form-control" value="{{ old('repository_url', $project->repository_url) }}"></div>
                        <div class="col-12"><label>Documentation URL</label><input name="documentation_url" class="form-control" value="{{ old('documentation_url', $project->documentation_url) }}"></div>
                    </div>
                </div>
            </div>

            <div class="pm-card">
                <details class="pm-details">
                    <summary><i class="fa-solid fa-sliders me-1"></i> Dados avançados de identidade</summary>
                    <div class="pm-details-content row g-3">
                        <div class="col-md-6"><label>Slogan</label><input name="slogan" class="form-control" value="{{ old('slogan', $project->slogan) }}"></div>
                        <div class="col-md-6"><label>Logo / URL</label><input name="logo" class="form-control" value="{{ old('logo', $project->logo) }}"></div>
                        <div class="col-md-4"><label>Primary color</label><input name="primary_color" class="form-control" value="{{ old('primary_color', $project->primary_color) }}"></div>
                        <div class="col-md-4"><label>Secondary color</label><input name="secondary_color" class="form-control" value="{{ old('secondary_color', $project->secondary_color) }}"></div>
                        <div class="col-md-4"><label>Accent color</label><input name="accent_color" class="form-control" value="{{ old('accent_color', $project->accent_color) }}"></div>
                        <div class="col-md-6"><label>Font family</label><input name="font_family" class="form-control" value="{{ old('font_family', $project->font_family) }}"></div>
                        <div class="col-md-3"><label>Staging URL</label><input name="staging_url" class="form-control" value="{{ old('staging_url', $project->staging_url) }}"></div>
                        <div class="col-md-3"><label>Production URL</label><input name="production_url" class="form-control" value="{{ old('production_url', $project->production_url) }}"></div>
                        <div class="col-12"><label>Brand notes</label><textarea name="brand_notes" class="form-control" rows="3">{{ old('brand_notes', $project->brand_notes) }}</textarea></div>
                        <div class="col-12"><label>Structure notes</label><textarea name="structure_notes" class="form-control" rows="3">{{ old('structure_notes', $project->structure_notes) }}</textarea></div>
                    </div>
                </details>
            </div>
        </form>
    </div>
</div>
@endsection
