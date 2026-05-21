@php
    $uploadId = $uploadId ?? 'dmsQuickUpload';
    $asModal = !empty($modal);
    $showButton = !array_key_exists('showButton', get_defined_vars()) || $showButton;
    $buttonLabel = $buttonLabel ?? 'Documento';
    $contextWorkspace = $workspace ?? $defaultWorkspace ?? null;
    $contextFolder = $folder ?? $defaultFolder ?? null;
    $sourceCandidate = $sourceModule ?? $contextWorkspace ?? 'document-manager';
    $quickUploadSourceModule = is_object($sourceCandidate)
        ? ($sourceCandidate->slug ?? $sourceCandidate->name ?? 'document-manager')
        : (is_array($sourceCandidate) ? ($sourceCandidate['slug'] ?? $sourceCandidate['name'] ?? 'document-manager') : $sourceCandidate);

    if (!isset($workspaces) && class_exists(\Modules\DocumentManager\Support\DocumentTable::class)) {
        $workspaces = \Modules\DocumentManager\Support\DocumentTable::safeGet('document_core_workspaces', fn ($query) => $query->where('is_active', true)->orderBy('name'));
    }

    $hasDocumentCategories = isset($categories)
        && collect($categories)->contains(fn ($category) => is_object($category) && isset($category->id, $category->name));

    if ($hasDocumentCategories) {
        $documentCategories = collect($categories);
    } elseif (class_exists(\Modules\DocumentManager\Support\DocumentTable::class)) {
        $documentCategories = \Modules\DocumentManager\Support\DocumentTable::safeGet('document_core_categories', fn ($query) => $query->orderBy('name'));
    } else {
        $documentCategories = collect();
    }

    if (!isset($folders) && class_exists(\Modules\DocumentManager\Support\DocumentTable::class)) {
        $folders = \Modules\DocumentManager\Support\DocumentTable::safeGet('document_core_folders', fn ($query) => $query->orderBy('path')->orderBy('name'));
    }

    $selectedWorkspace = old('workspace_id', $workspaceId ?? null);
    if (!$selectedWorkspace && $contextWorkspace) {
        $selectedWorkspace = collect($workspaces ?? [])->firstWhere('slug', $contextWorkspace)?->id
            ?? collect($workspaces ?? [])->firstWhere('name', $contextWorkspace)?->id;
    }

    $selectedFolder = old('folder_id', $folderId ?? null);
    if (!$selectedFolder && $contextFolder) {
        $selectedFolder = collect($folders ?? [])->firstWhere('slug', $contextFolder)?->id
            ?? collect($folders ?? [])->firstWhere('path', $contextFolder)?->id
            ?? collect($folders ?? [])->firstWhere('name', $contextFolder)?->id;
    }
@endphp

@if($asModal && $showButton)
    <button type="button" class="btn btn-outline-primary dms-entry-button" data-bs-toggle="modal" data-bs-target="#{{ $uploadId }}Modal">
        <i class="fa-solid fa-file-circle-plus"></i> {{ $buttonLabel }}
    </button>
@endif

@if($asModal)
    @include('documentmanager::partials.quick-upload-modal', get_defined_vars())
@else
    @include('documentmanager::partials.quick-upload-dashboard', get_defined_vars())
@endif
