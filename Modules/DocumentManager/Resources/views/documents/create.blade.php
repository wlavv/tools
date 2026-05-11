@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <form method="POST" action="{{ route('document-manager.documents.store') }}" enctype="multipart/form-data" class="dms-form-grid" id="lsg-form">
        @csrf

        <div class="dms-card">
            <div class="dms-card__head">
                <div>
                    <span class="dms-eyebrow">Master object</span>
                    <h3>Novo documento</h3>
                </div>
            </div>

            @include('documentmanager::documents.form', ['document' => null])
        </div>

        <details class="dms-card dms-collapsible-upload">
            <summary>
                <span>
                    <span class="dms-eyebrow">Storage layer</span>
                    <strong>Upload</strong>
                </span>
                <i class="fa-solid fa-cloud-arrow-up"></i>
            </summary>

            <label class="dms-upload-zone" for="dmsFileInput">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <strong>Arrastar ou selecionar ficheiro</strong>
                <span>PDF, imagens, Office, markdown, video e anexos operacionais.</span>
                <input id="dmsFileInput" type="file" name="file">
            </label>

        </details>
    </form>
@endsection
