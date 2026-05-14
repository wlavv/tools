@extends('data-import-wizard::layout')

@section('module-content')
    <h1>Importar: {{ $profile['label'] }}</h1>

    <div class="card">
        <form method="post" enctype="multipart/form-data" action="{{ route('data_import_wizard.profiles.upload.store', $profile['key']) }}">
            @csrf

            <p>
                <label for="file"><strong>CSV</strong></label><br>
                <input type="file" name="file" id="file" required>
            </p>

            <p>
                <label for="mode"><strong>Modo</strong></label><br>
                <select name="mode" id="mode">
                    @foreach ($modes as $mode)
                        <option value="{{ $mode }}" @selected($mode === config('data-import-wizard.default_mode', 'upsert'))>{{ $mode }}</option>
                    @endforeach
                </select>
            </p>

            <button type="submit" class="btn">Validar CSV</button>
            <a class="btn secondary" href="{{ route('data_import_wizard.profiles.template', $profile['key']) }}">Descarregar template</a>
        </form>
    </div>
@endsection
