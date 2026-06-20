@extends('site-manager::layouts.module')

@section('module-content')
    @php
        $settings = is_array($item->settings ?? null) ? $item->settings : [];
        $logoUrl = $settings['logo_url'] ?? $settings['logo'] ?? $settings['image'] ?? null;
    @endphp
    <div class="sm-card">
        <form id="lsg-form" class="sm-form" method="POST" enctype="multipart/form-data" action="{{ $item->exists ? route('lsg.site_manager.sites.update', $item) : route('lsg.site_manager.sites.store') }}">
            @csrf
            @if($item->exists) @method('PUT') @endif

            <div class="sm-form-grid">
                <div class="sm-form-grid-full">
                    <label class="sm-label">Logo</label>
                    <div class="sm-logo-upload">
                        <div class="sm-logo-upload__preview">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Logo {{ $item->name }}">
                            @else
                                <i class="fa-solid fa-store"></i>
                            @endif
                        </div>
                        <div class="sm-logo-upload__body">
                            <input class="sm-input" type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                            <small>PNG, JPG, WEBP ou SVG ate 2MB. O link fica guardado nos settings da loja/site.</small>
                            @if($logoUrl)
                                <a href="{{ $logoUrl }}" target="_blank" rel="noopener noreferrer">{{ $logoUrl }}</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div>
                    <label class="sm-label">Nome</label>
                    <input class="sm-input" name="name" value="{{ old('name', $item->name) }}" required>
                </div>
                <div>
                    <label class="sm-label">Slug</label>
                    <input class="sm-input" name="slug" value="{{ old('slug', $item->slug) }}">
                </div>
                <div>
                    <label class="sm-label">Tipo</label>
                    <select class="sm-select" name="site_type" required>
                        @foreach(config('site-manager.site_types') as $key => $label)
                            <option value="{{ $key }}" @selected(old('site_type', $item->site_type ?: 'store') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sm-label">Estado</label>
                    <select class="sm-select" name="status" required>
                        @foreach(['active' => 'Ativo', 'inactive' => 'Inativo', 'maintenance' => 'Manutencao', 'archived' => 'Arquivado'] as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $item->status ?: 'active') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sm-label">Dominio</label>
                    <input class="sm-input" name="domain" value="{{ old('domain', $item->domain) }}" placeholder="example.com">
                </div>
                <div>
                    <label class="sm-label">URL publica</label>
                    <input class="sm-input" name="public_url" value="{{ old('public_url', $item->public_url) }}" placeholder="https://example.com">
                </div>
                <div>
                    <label class="sm-label">Ambiente</label>
                    <select class="sm-select" name="environment" required>
                        @foreach(['production' => 'Producao', 'staging' => 'Staging', 'development' => 'Desenvolvimento'] as $key => $label)
                            <option value="{{ $key }}" @selected(old('environment', $item->environment ?: 'production') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sm-label">Projeto</label>
                    <input class="sm-input" name="project_id" value="{{ old('project_id', $item->project_id) }}" type="number">
                </div>
                <div>
                    <label class="sm-label">Idioma</label>
                    <input class="sm-input" name="default_language" value="{{ old('default_language', $item->default_language ?: 'pt') }}">
                </div>
                <div>
                    <label class="sm-label">Moeda</label>
                    <input class="sm-input" name="default_currency" value="{{ old('default_currency', $item->default_currency ?: 'EUR') }}" maxlength="3">
                </div>
                <div class="sm-form-grid-full">
                    <label class="sm-label">Monitorizacao</label>
                    <div class="sm-checks">
                        <label><input type="checkbox" name="monitor_pagespeed" value="1" @checked(old('monitor_pagespeed', $item->monitor_pagespeed ?? true))> PageSpeed diário</label>
                        <label><input type="checkbox" name="monitor_availability" value="1" @checked(old('monitor_availability', $item->monitor_availability ?? true))> Disponibilidade</label>
                    </div>
                </div>
                <div class="sm-form-grid-full">
                    <label class="sm-label">Notas</label>
                    <textarea class="sm-textarea" name="notes" rows="4">{{ old('notes', $item->notes) }}</textarea>
                </div>
            </div>
        </form>
    </div>
@endsection
