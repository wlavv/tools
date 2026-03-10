<div class="pm-grid pm-grid-2">
    <div class="pm-info-box">
        <h4>Dados base</h4>
        <div class="mb-3">
            <label>Nome</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $project->name ?? '') }}" required>
        </div>
        <div class="mb-3">
            <label>Slug</label>
            <input type="text" name="slug" class="form-control" value="{{ old('slug', $project->slug ?? '') }}">
        </div>
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control" required>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $project->status ?? 'New') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Projeto pai</label>
            <select name="id_parent" class="form-control">
                <option value="0">Sem parent</option>
                @foreach($projects as $parentProject)
                    <option value="{{ $parentProject->id }}" @selected((int) old('id_parent', $project->id_parent ?? 0) === (int) $parentProject->id)>{{ $parentProject->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Prioridade</label>
            <input type="number" name="priority" class="form-control" min="0" value="{{ old('priority', $project->priority ?? 0) }}">
        </div>
        <div class="mb-3">
            <label>URL</label>
            <input type="url" name="url" class="form-control" value="{{ old('url', $project->url ?? '') }}">
        </div>
        <div class="mb-3">
            <label>Logo</label>
            <input type="text" name="logo" class="form-control" value="{{ old('logo', $project->logo ?? '') }}">
        </div>
        <div class="mb-3">
            <label>Descrição</label>
            <textarea name="description" class="form-control" rows="5">{{ old('description', $project->description ?? '') }}</textarea>
        </div>
    </div>

    <div class="pm-info-box">
        <h4>Branding e planeamento</h4>
        <div class="mb-3"><label>Primary color</label><input type="text" name="primary_color" class="form-control" value="{{ old('primary_color', $project->primary_color ?? '') }}"></div>
        <div class="mb-3"><label>Secondary color</label><input type="text" name="secondary_color" class="form-control" value="{{ old('secondary_color', $project->secondary_color ?? '') }}"></div>
        <div class="mb-3"><label>Accent color</label><input type="text" name="accent_color" class="form-control" value="{{ old('accent_color', $project->accent_color ?? '') }}"></div>
        <div class="mb-3"><label>Font family</label><input type="text" name="font_family" class="form-control" value="{{ old('font_family', $project->font_family ?? '') }}"></div>
        <div class="mb-3"><label>Brand notes</label><textarea name="brand_notes" class="form-control" rows="4">{{ old('brand_notes', $project->brand_notes ?? '') }}</textarea></div>
        <div class="mb-3"><label>Start date</label><input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($project->start_date ?? null)->format('Y-m-d')) }}"></div>
        <div class="mb-3"><label>Deadline</label><input type="date" name="deadline" class="form-control" value="{{ old('deadline', optional($project->deadline ?? null)->format('Y-m-d')) }}"></div>
    </div>

    <div class="pm-info-box">
        <h4>Contacts</h4>
        <div class="mb-3"><label>Contact name</label><input type="text" name="contact_name" class="form-control" value="{{ old('contact_name', $project->contact_name ?? '') }}"></div>
        <div class="mb-3"><label>Contact email</label><input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $project->contact_email ?? '') }}"></div>
        <div class="mb-3"><label>Contact phone</label><input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $project->contact_phone ?? '') }}"></div>
        <div class="mb-3"><label>Website</label><input type="url" name="website" class="form-control" value="{{ old('website', $project->website ?? '') }}"></div>
        <div class="mb-3"><label>Facebook</label><input type="url" name="social_facebook" class="form-control" value="{{ old('social_facebook', $project->social_facebook ?? '') }}"></div>
        <div class="mb-3"><label>Instagram</label><input type="url" name="social_instagram" class="form-control" value="{{ old('social_instagram', $project->social_instagram ?? '') }}"></div>
        <div class="mb-3"><label>LinkedIn</label><input type="url" name="social_linkedin" class="form-control" value="{{ old('social_linkedin', $project->social_linkedin ?? '') }}"></div>
        <div class="mb-3"><label>YouTube</label><input type="url" name="social_youtube" class="form-control" value="{{ old('social_youtube', $project->social_youtube ?? '') }}"></div>
    </div>

    <div class="pm-info-box">
        <h4>Team e documentação</h4>
        <div class="mb-3"><label>Repository URL</label><input type="url" name="repository_url" class="form-control" value="{{ old('repository_url', $project->repository_url ?? '') }}"></div>
        <div class="mb-3"><label>Documentation URL</label><input type="url" name="documentation_url" class="form-control" value="{{ old('documentation_url', $project->documentation_url ?? '') }}"></div>
        <div class="mb-3"><label>Team notes</label><textarea name="team_notes" class="form-control" rows="3">{{ old('team_notes', $project->team_notes ?? '') }}</textarea></div>
        <div class="mb-3"><label>Team JSON</label><textarea name="team_json" class="form-control" rows="4">{{ old('team_json', $project->team_json ?? '') }}</textarea></div>
        <div class="mb-3"><label>Structure notes</label><textarea name="structure_notes" class="form-control" rows="3">{{ old('structure_notes', $project->structure_notes ?? '') }}</textarea></div>
        <div class="mb-3"><label>Documentation notes</label><textarea name="documentation_notes" class="form-control" rows="3">{{ old('documentation_notes', $project->documentation_notes ?? '') }}</textarea></div>
    </div>
</div>

<div class="mt-3 d-flex gap-2">
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('project_manager.index') }}" class="btn btn-secondary">Cancelar</a>
</div>
