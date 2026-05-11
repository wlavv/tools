@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <form method="POST" action="{{ route('document-manager.categories.store') }}" class="dms-form-grid dms-form-grid--single" id="lsg-form">
        @csrf
        <div class="dms-card">
            <div class="dms-card__head"><div><span class="dms-eyebrow">Categoria</span><h3>Nova categoria</h3></div></div>
            @include('documentmanager::categories.form', ['category' => null])
        </div>
    </form>
@endsection
