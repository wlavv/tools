@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <form method="POST" action="{{ route('document-manager.workspaces.update', $workspace->id) }}" class="dms-form-grid dms-form-grid--single" id="lsg-form">
        @csrf
        @method('PUT')
        <div class="dms-card">
            <div class="dms-card__head">
                <div>
                    <span class="dms-eyebrow">Workspace</span>
                    <h3>Editar workspace</h3>
                </div>
            </div>
            @include('documentmanager::workspaces.form', ['workspace' => $workspace])
        </div>
    </form>
@endsection
