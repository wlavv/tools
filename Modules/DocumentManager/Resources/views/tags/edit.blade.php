@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <form method="POST" action="{{ route('document-manager.tags.update', $tag->id) }}" class="dms-form-grid dms-form-grid--single" id="lsg-form">
        @csrf
        @method('PUT')
        <div class="dms-card">
            <div class="dms-card__head"><div><span class="dms-eyebrow">Tag</span><h3>Editar tag</h3></div></div>
            @include('documentmanager::tags.form', ['tag' => $tag])
        </div>
    </form>
@endsection
