@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <form method="POST" action="{{ route('document-manager.folders.update', $folder->id) }}" class="dms-form-grid dms-form-grid--single" id="lsg-form">
        @csrf
        @method('PUT')
        <div class="dms-card">
            <div class="dms-card__head"><div><span class="dms-eyebrow">Folder</span><h3>Editar pasta</h3></div></div>
            @include('documentmanager::folders.form', ['folder' => $folder])
        </div>
    </form>
@endsection
