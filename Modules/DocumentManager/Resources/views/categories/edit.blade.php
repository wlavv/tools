@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <form method="POST" action="{{ route('document-manager.categories.update', $category->id) }}" class="dms-form-grid dms-form-grid--single" id="lsg-form">
        @csrf
        @method('PUT')
        <div class="dms-card">
            <div class="dms-card__head"><div><span class="dms-eyebrow">Categoria</span><h3>Editar categoria</h3></div></div>
            @include('documentmanager::categories.form', ['category' => $category])
        </div>
    </form>
@endsection
