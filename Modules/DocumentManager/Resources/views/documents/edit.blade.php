@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <div class="dms-document-edit-workspace">
        <div class="dms-card dms-document-edit-preview">
            <div class="dms-card__head">
                <div>
                    <span class="dms-eyebrow">Documento</span>
                    <h3>Preview</h3>
                </div>
            </div>

            @include('documentmanager::documents.preview-inline', [
                'document' => $document,
                'currentVersion' => $currentVersion ?? null,
                'fileAvailable' => $fileAvailable ?? false,
                'compact' => true,
            ])
        </div>

        <form method="POST" action="{{ route('document-manager.documents.update', $document) }}" class="dms-card dms-document-edit-form" id="lsg-form">
            @csrf
            @method('PUT')

            <div class="dms-card__head">
                <div>
                    <span class="dms-eyebrow">Master object</span>
                    <h3>Editar documento</h3>
                </div>
            </div>

            @include('documentmanager::documents.form', ['document' => $document, 'compact' => true])
        </form>
    </div>
@endsection
