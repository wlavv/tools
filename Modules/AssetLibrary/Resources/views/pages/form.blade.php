@extends('layouts.app')

@section('content')
    @include('asset-library::Includes.css')
    @include('asset-library::Includes.js')

    <div class="al-page">
        @include('asset-library::Includes._components.flash')

        <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="al-form-grid">
            @csrf
            @if($httpMethod !== 'POST')
                @method($httpMethod)
            @endif

            <div class="al-form-card al-panel">
                <div class="al-field-grid">
                    <div class="al-field">
                        <label for="name">Nome</label>
                        <input id="name" name="name" type="text" class="al-input" value="{{ old('name', $asset->name) }}" required>
                        @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="al-field">
                        <label for="type">Tipo</label>
                        <select id="type" name="type" class="al-select" required>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" @selected(old('type', $asset->type) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="al-field">
                        <label for="status">Estado</label>
                        <select id="status" name="status" class="al-select" required>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $asset->status ?: 'draft') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="al-field">
                        <label for="tags">Tags</label>
                        <input id="tags" name="tags" type="text" class="al-input" value="{{ old('tags', $asset->tags) }}" placeholder="ar, glb, interior, product">
                        @error('tags') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="al-field al-field-full">
                        <label for="description">Descrição</label>
                        <textarea id="description" name="description" class="al-textarea">{{ old('description', $asset->description) }}</textarea>
                        @error('description') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="al-field">
                        <label for="alt_text">Alt text</label>
                        <input id="alt_text" name="alt_text" type="text" class="al-input" value="{{ old('alt_text', $asset->alt_text) }}">
                        @error('alt_text') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="al-field">
                        <label for="file">Ficheiro</label>
                        <input id="file" name="file" type="file" class="al-input" data-al-file-input {{ $httpMethod === 'POST' ? 'required' : '' }}>
                        <div class="al-muted" data-al-file-preview-name data-empty-text="Sem ficheiro selecionado">
                            {{ $asset->original_filename ?: 'Sem ficheiro selecionado' }}
                        </div>
                        @error('file') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="al-field al-field-full">
                        <label class="al-checkline">
                            <input type="hidden" name="is_public" value="0">
                            <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $asset->is_public))>
                            <span>Disponível publicamente</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="al-form-card al-panel">
                <div class="al-field" style="margin-bottom:1rem;">
                    <strong>Resumo</strong>
                </div>

                <div class="al-kv">
                    <div>Registo</div>
                    <div>{{ $asset->exists ? 'Edição' : 'Criação' }}</div>
                </div>
                <div class="al-kv">
                    <div>Slug</div>
                    <div>{{ $asset->slug ?: 'Será gerado automaticamente' }}</div>
                </div>
                <div class="al-kv">
                    <div>Ficheiro atual</div>
                    <div>{{ $asset->original_filename ?: 'Ainda sem ficheiro' }}</div>
                </div>

                <div class="al-actions" style="margin-top:1rem;">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="{{ route('asset_library.index') }}" class="btn btn-outline-secondary">Voltar</a>
                    @if($asset->exists)
                        <a href="{{ route('asset_library.show', $asset) }}" class="btn btn-outline-secondary">Detalhe</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
@endsection
