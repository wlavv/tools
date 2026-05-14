@extends('translation-manager::layouts.module')

@section('content')
@php
    $statusMeta = [
        'base_only' => ['class' => 'tm-badge tm-badge-blue', 'icon' => 'fa-layer-group'],
        'partial' => ['class' => 'tm-badge tm-badge-warning', 'icon' => 'fa-triangle-exclamation'],
        'custom_full' => ['class' => 'tm-badge tm-badge-success', 'icon' => 'fa-check-circle'],
        'has_empty' => ['class' => 'tm-badge tm-badge-danger', 'icon' => 'fa-circle-xmark'],
        'has_extra' => ['class' => 'tm-badge tm-badge-purple', 'icon' => 'fa-code-compare'],
        'base' => ['class' => 'tm-badge tm-badge-blue', 'icon' => 'fa-layer-group'],
        'empty' => ['class' => 'tm-badge tm-badge-danger', 'icon' => 'fa-circle-xmark'],
        'custom_changed' => ['class' => 'tm-badge tm-badge-success', 'icon' => 'fa-check-circle'],
        'custom_same' => ['class' => 'tm-badge tm-badge-success', 'icon' => 'fa-check-circle'],
    ];
@endphp

<style>
    .tm-wrap { display:grid; grid-template-columns: 320px minmax(0,1fr); gap:16px; }
    .tm-card { background: var(--bs-body-bg,#fff); border:1px solid rgba(120,120,120,.18); border-radius:5px; box-shadow:0 10px 24px rgba(0,0,0,.06); }
    .tm-card-header { padding:14px 16px; border-bottom:1px solid rgba(120,120,120,.14); display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .tm-card-body { padding:16px; }
    .tm-module-list { display:flex; flex-direction:column; gap:8px; }
    .tm-module-search { margin-bottom:12px; }
    .tm-module-item { display:block; padding:12px; border:1px solid rgba(120,120,120,.16); border-radius:5px; text-decoration:none; color:inherit; background:rgba(120,120,120,.035); }
    .tm-module-item.active { outline:2px solid rgba(13,110,253,.35); background:rgba(13,110,253,.08); }
    .tm-module-name { font-weight:700; display:flex; align-items:center; justify-content:space-between; gap:8px; }
    .tm-module-meta { margin-top:8px; display:flex; flex-wrap:wrap; gap:6px; font-size:12px; opacity:.92; }
    .tm-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 8px; border-radius:5px; font-size:11px; font-weight:700; line-height:1; white-space:nowrap; }
    .tm-badge-success { color:#146c43; background:rgba(25,135,84,.14); }
    .tm-badge-warning { color:#997404; background:rgba(255,193,7,.18); }
    .tm-badge-danger { color:#b02a37; background:rgba(220,53,69,.14); }
    .tm-badge-blue { color:#084298; background:rgba(13,110,253,.12); }
    .tm-badge-purple { color:#5a2d82; background:rgba(111,66,193,.14); }
    .tm-toolbar { display:flex; gap:10px; flex-wrap:wrap; align-items:end; }
    .tm-toolbar .form-group { min-width:170px; }
    .tm-stats { margin:14px 0; }
    .prm-dashboard-grid { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:12px; margin-bottom:10px; }
    .prm-dashboard-metric { position:relative; overflow:hidden; border-radius:0; padding:16px; border:1px solid rgba(148,163,184,.25); background:linear-gradient(135deg,rgba(255,255,255,.96),rgba(248,250,252,.86)); box-shadow:0 8px 24px rgba(15,23,42,.08); display:flex; justify-content:space-between; gap:14px; align-items:center; }
    .prm-dashboard-metric__label { font-size:12px; text-transform:uppercase; color:#64748b; font-weight:800; letter-spacing:.04em; }
    .prm-dashboard-metric__value { font-size:30px; line-height:1; font-weight:900; color:#0f172a; margin-top:6px; }
    .prm-dashboard-metric__icon { width:46px; height:46px; border-radius:0; display:flex; align-items:center; justify-content:center; background:color-mix(in srgb,var(--metric-color,#2563eb) 16%,transparent); color:var(--metric-color,#2563eb); font-size:20px; border:1px solid color-mix(in srgb,var(--metric-color,#2563eb) 28%,transparent); flex:0 0 46px; }
    .prm-dashboard-metric.roles { --metric-color:#2563eb; }
    .prm-dashboard-metric.permissions { --metric-color:#7c3aed; }
    .prm-dashboard-metric.critical { --metric-color:#dc2626; }
    .prm-dashboard-metric.users { --metric-color:#16a34a; }
    .tm-table-wrap { overflow:auto; border:1px solid rgba(120,120,120,.16); border-radius:5px; }
    .tm-table { width:100%; border-collapse:collapse; min-width:900px; }
    .tm-table th, .tm-table td { padding:10px 12px; border-bottom:1px solid rgba(120,120,120,.12); vertical-align:middle; }
    .tm-table th { font-size:12px; text-transform:uppercase; letter-spacing:.04em; background:rgba(120,120,120,.055); }
    .tm-key { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size:12px; white-space:nowrap; }
    .tm-base { font-size:12px; opacity:.75; max-width:340px; }
    .tm-input { width:100%; border:1px solid rgba(120,120,120,.22); border-radius:5px; padding:8px 10px; background:var(--bs-body-bg,#fff); color:inherit; }
    .tm-actions { display:flex; gap:8px; justify-content:flex-end; margin-top:14px; }
    .tm-extra { margin-top:18px; }
    @media (max-width: 1199px) { .prm-dashboard-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width: 992px) { .tm-wrap { grid-template-columns:1fr; } }
    @media (max-width: 767px) { .prm-dashboard-grid { grid-template-columns:1fr; } }
</style>

<div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="tm-wrap">
        <aside class="tm-card">
            <div class="tm-card-header">
                <strong>{{ __('translation-manager::messages.labels.module') }}</strong>
                <span class="tm-badge tm-badge-blue"><i class="fa-solid fa-cubes"></i>{{ count($modules) }}</span>
            </div>
            <div class="tm-card-body">
                <div class="tm-module-search">
                    <label class="form-label" for="tmModuleSearch">{{ __('translation-manager::messages.labels.search') }} módulos</label>
                    <input type="search" id="tmModuleSearch" class="form-control" placeholder="Pesquisar módulos">
                </div>
                <div class="tm-module-list">
                    @forelse($modules as $module)
                        @php $stats = $module['stats']; @endphp
                        <a class="tm-module-item {{ $selectedModuleSlug === $module['slug'] ? 'active' : '' }}"
                           data-module-search="{{ strtolower($module['name'] . ' ' . $module['slug']) }}"
                           href="{{ route('translation_manager.index', ['module' => $module['slug'], 'locale' => $locale]) }}">
                            <div class="tm-module-name">
                                <span>{{ $module['name'] }}</span>
                                @if(($stats['missing_total'] ?? 0) > 0)
                                    <span class="tm-badge tm-badge-warning"><i class="fa-solid fa-triangle-exclamation"></i>{{ $stats['missing_total'] }}</span>
                                @else
                                    <span class="tm-badge tm-badge-success"><i class="fa-solid fa-check-circle"></i>OK</span>
                                @endif
                            </div>
                            <div class="tm-module-meta">
                                <span>{{ $stats['files'] ?? 0 }} {{ __('translation-manager::messages.stats.files') }}</span>
                                <span>{{ $stats['base_total'] ?? 0 }} tags</span>
                                @if(($stats['base_only_files'] ?? 0) > 0)
                                    <span class="tm-badge tm-badge-blue"><i class="fa-solid fa-file-circle-xmark"></i>{{ $stats['base_only_files'] }} base only</span>
                                @endif
                                @if(($stats['extra_total'] ?? 0) > 0)
                                    <span class="tm-badge tm-badge-purple"><i class="fa-solid fa-code-compare"></i>{{ $stats['extra_total'] }} extra</span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="text-muted">Nenhum módulo encontrado.</div>
                    @endforelse
                </div>
            </div>
        </aside>

        <main class="tm-card">
            <div class="tm-card-header">
                <div>
                    <strong>{{ $selectedModule['name'] ?? '—' }}</strong>
                    @if($payload)
                        @php $meta = $statusMeta[$payload['status']] ?? $statusMeta['base']; @endphp
                        <span class="{{ $meta['class'] }} ms-2"><i class="fa-solid {{ $meta['icon'] }}"></i>{{ __('translation-manager::messages.statuses.' . $payload['status']) }}</span>
                    @endif
                </div>
            </div>
            <div class="tm-card-body">
                @if($selectedModule)
                    <form method="GET" action="{{ route('translation_manager.index') }}" class="tm-toolbar mb-3">
                        <input type="hidden" name="module" value="{{ $selectedModule['slug'] }}">
                        <div class="form-group">
                            <label class="form-label">{{ __('translation-manager::messages.labels.locale') }}</label>
                            <select name="locale" class="form-select" onchange="this.form.submit()">
                                @foreach($locales as $key => $label)
                                    <option value="{{ $key }}" @selected($key === $locale)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('translation-manager::messages.labels.file') }}</label>
                            <select name="file" class="form-select" onchange="this.form.submit()">
                                @foreach($files as $file)
                                    <option value="{{ $file['file'] }}" @selected($file['file'] === $selectedFile)>{{ $file['file'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group flex-grow-1">
                            <label class="form-label" for="tmTagSearch">{{ __('translation-manager::messages.labels.search') }} tags</label>
                            <input type="search" id="tmTagSearch" class="form-control" placeholder="Pesquisar tags">
                        </div>
                    </form>

                    @if($payload)
                        @php $s = $payload['stats']; @endphp
                        <div class="tm-stats prm-dashboard-grid">
                            <div class="prm-dashboard-metric roles">
                                <div><div class="prm-dashboard-metric__label">{{ __('translation-manager::messages.stats.base') }}</div><div class="prm-dashboard-metric__value">{{ $s['base_total'] }}</div></div>
                                <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-layer-group"></i></div>
                            </div>
                            <div class="prm-dashboard-metric users">
                                <div><div class="prm-dashboard-metric__label">{{ __('translation-manager::messages.stats.custom') }}</div><div class="prm-dashboard-metric__value">{{ $s['custom_total'] }}</div></div>
                                <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-pen-to-square"></i></div>
                            </div>
                            <div class="prm-dashboard-metric critical">
                                <div><div class="prm-dashboard-metric__label">{{ __('translation-manager::messages.stats.missing') }}</div><div class="prm-dashboard-metric__value">{{ $s['missing_total'] }}</div></div>
                                <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                            </div>
                            <div class="prm-dashboard-metric permissions">
                                <div><div class="prm-dashboard-metric__label">{{ __('translation-manager::messages.stats.empty') }}</div><div class="prm-dashboard-metric__value">{{ $s['empty_total'] }}</div></div>
                                <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-circle-xmark"></i></div>
                            </div>
                            <div class="prm-dashboard-metric roles">
                                <div><div class="prm-dashboard-metric__label">{{ __('translation-manager::messages.stats.extra') }}</div><div class="prm-dashboard-metric__value">{{ $s['extra_total'] }}</div></div>
                                <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-code-compare"></i></div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('translation_manager.save') }}">
                            @csrf
                            <input type="hidden" name="module" value="{{ $selectedModule['slug'] }}">
                            <input type="hidden" name="locale" value="{{ $locale }}">
                            <input type="hidden" name="file" value="{{ $selectedFile }}">

                            <div class="tm-table-wrap">
                                <table class="tm-table" id="tmTable">
                                    <thead>
                                        <tr>
                                            <th>{{ __('translation-manager::messages.labels.tag') }}</th>
                                            <th>{{ __('translation-manager::messages.labels.translation') }}</th>
                                            <th>{{ __('translation-manager::messages.labels.base') }}</th>
                                            <th>{{ __('translation-manager::messages.labels.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payload['rows'] as $row)
                                            @php $meta = $statusMeta[$row['status']] ?? $statusMeta['base']; @endphp
                                            <tr data-search="{{ strtolower($row['key'] . ' ' . $row['value'] . ' ' . $row['base']) }}">
                                                <td class="tm-key">{{ $row['key'] }}</td>
                                                <td>
                                                    <input class="tm-input" name="translations[{{ $row['key'] }}]" value="{{ $row['value'] }}">
                                                </td>
                                                <td class="tm-base">{{ $row['base'] }}</td>
                                                <td>
                                                    <span class="{{ $meta['class'] }}"><i class="fa-solid {{ $meta['icon'] }}"></i>{{ __('translation-manager::messages.statuses.' . $row['status']) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="tm-actions">
                                @if($payload['custom_exists'])
                                    <button type="submit" form="removeOverrideForm" class="btn btn-outline-danger" onclick="return confirm('Remover o ficheiro custom deste módulo/idioma/ficheiro?')">
                                        <i class="fa-solid fa-trash"></i> {{ __('translation-manager::messages.actions.remove_override') }}
                                    </button>
                                @endif
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fa-solid fa-floppy-disk"></i> {{ __('translation-manager::messages.actions.save') }}
                                </button>
                            </div>
                        </form>

                        <form id="removeOverrideForm" method="POST" action="{{ route('translation_manager.remove_override') }}" class="d-none">
                            @csrf
                            <input type="hidden" name="module" value="{{ $selectedModule['slug'] }}">
                            <input type="hidden" name="locale" value="{{ $locale }}">
                            <input type="hidden" name="file" value="{{ $selectedFile }}">
                        </form>

                        @if(count($payload['extra']) > 0)
                            <div class="tm-extra">
                                <h5>{{ __('translation-manager::messages.labels.extra_keys') }}</h5>
                                <div class="tm-table-wrap">
                                    <table class="tm-table">
                                        <tbody>
                                            @foreach($payload['extra'] as $key => $value)
                                                <tr>
                                                    <td class="tm-key">{{ $key }}</td>
                                                    <td>{{ $value }}</td>
                                                    <td style="width:120px;">
                                                        <form method="POST" action="{{ route('translation_manager.remove_extra_key') }}">
                                                            @csrf
                                                            <input type="hidden" name="module" value="{{ $selectedModule['slug'] }}">
                                                            <input type="hidden" name="locale" value="{{ $locale }}">
                                                            <input type="hidden" name="file" value="{{ $selectedFile }}">
                                                            <input type="hidden" name="key" value="{{ $key }}">
                                                            <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning">Este módulo não tem ficheiros de tradução para o idioma selecionado.</div>
                    @endif
                @else
                    <div class="alert alert-info">Seleciona um módulo para começar.</div>
                @endif
            </div>
        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const moduleSearch = document.getElementById('tmModuleSearch');
        const moduleItems = document.querySelectorAll('.tm-module-item');
        const search = document.getElementById('tmTagSearch');
        const rows = document.querySelectorAll('#tmTable tbody tr');

        if (moduleSearch) {
            moduleSearch.addEventListener('input', function () {
                const q = this.value.trim().toLowerCase();
                moduleItems.forEach(item => {
                    item.style.display = item.dataset.moduleSearch.includes(q) ? '' : 'none';
                });
            });
        }

        if (!search) return;

        search.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            rows.forEach(row => {
                row.style.display = row.dataset.search.includes(q) ? '' : 'none';
            });
        });
    });
</script>
@endsection
