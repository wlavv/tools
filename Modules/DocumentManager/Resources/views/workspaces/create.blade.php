@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <form method="POST" action="{{ route('document-manager.workspaces.store') }}" class="dms-form-grid dms-form-grid--single" id="lsg-form">
        @csrf
        <div class="dms-card">
            <div class="dms-card__head">
                <div>
                    <span class="dms-eyebrow">Workspace</span>
                    <h3>Novo workspace</h3>
                </div>
            </div>
            @include('documentmanager::workspaces.form', ['workspace' => null])
        </div>
    </form>
@endsection
