<div class="card idealab-card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Key</label>
                <input type="text" name="key" class="form-control" value="{{ old('key', $template->key) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $template->name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Entrypoint Type</label>
                <input type="text" name="entrypoint_type" class="form-control" value="{{ old('entrypoint_type', $template->entrypoint_type ?: 'idea_discussion') }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $template->description) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">System Prompt</label>
                <textarea name="system_prompt" class="form-control" rows="4">{{ old('system_prompt', $template->system_prompt) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">User Prompt Template</label>
                <textarea name="user_prompt_template" class="form-control" rows="8" required>{{ old('user_prompt_template', $template->user_prompt_template) }}</textarea>
                <div class="form-text">Available placeholders: @{{title}}, @{{description_raw}}, @{{description_refined}}.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $template->sort_order ?? 0) }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check">
                    <input type="hidden" name="supports_chat" value="0">
                    <input class="form-check-input" type="checkbox" name="supports_chat" value="1" @checked(old('supports_chat', $template->supports_chat ?? true))>
                    <label class="form-check-label">Supports chat</label>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active ?? true))>
                    <label class="form-check-label">Active</label>
                </div>
            </div>
        </div>
    </div>
</div>
