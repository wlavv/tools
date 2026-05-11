<div class="modal fade dms-modal" id="{{ $uploadId }}Modal" tabindex="-1" aria-hidden="true" data-backdrop="false" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span class="dms-eyebrow">Entrada rapida</span>
                    <h5 class="modal-title">Criar documento</h5>
                </div>
                <a href="{{ route('document-manager.dashboard') }}" class="btn btn-outline-primary btn-sm ms-auto ml-auto dms-modal-manager-link">
                    <i class="fa-solid fa-folder-tree"></i> Document Manager
                </a>
            </div>
            <div class="modal-body">
                @include('documentmanager::partials.quick-upload-form')
            </div>
        </div>
    </div>
</div>
