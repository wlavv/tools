@extends(config('project-manager.layout', 'layouts.app'))

@section('content')
@include('project-manager::partials.styles')
@php
    use Modules\ProjectManager\Services\ProjectManagerSectionRegistry;
    $visibleFields = array_slice($meta['list_fields'] ?? $meta['fields'], 0, 4);
    $tabActive = in_array($section, ['tasks','roadmap-items'], true) ? $section : 'details';
@endphp

<div class="lsg-content pm-wrap">
    <div class="pm-shell">
        @include('project-manager::partials.project-tabs', ['activeTab' => $tabActive])

        @if(session('success'))
            <div class="pm-alert">{{ session('success') }}</div>
        @endif

        <div class="pm-card">
            <div class="pm-section-bar">
                <div>
                    <div class="pm-card-title"><i class="{{ $meta['icon'] ?? 'fa-solid fa-table' }}"></i> {{ $meta['label'] }}</div>
                    <div class="pm-card-subtitle mb-0">{{ $meta['description'] ?? 'Lista operacional.' }}</div>
                </div>
            </div>

            <table class="pm-table lsg-datatable">
                <thead>
                    <tr>
                        @foreach($visibleFields as $field)
                            <th>{{ ucfirst(str_replace('_', ' ', $field)) }}</th>
                        @endforeach
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            @foreach($visibleFields as $field)
                                <td>
                                    @php($value = $record->{$field} ?? null)
                                    @if(in_array($field, ['status','priority','type','category','importance'], true))
                                        <span class="pm-pill {{ in_array($value, ['blocked','critical','high','open'], true) ? 'pm-pill--danger' : (in_array($value, ['done','completed','accepted','active','resolved'], true) ? 'pm-pill--ok' : 'pm-pill--gold') }}">{{ $value ?: '-' }}</span>
                                    @else
                                        <span title="{{ $value }}">{{ \Illuminate\Support\Str::limit((string)($value ?: '-'), 70) }}</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-end">
                                <div class="pm-actions pm-actions--right">
                                    <a class="pm-btn pm-btn--compact pm-btn--warning" href="{{ route(ProjectManagerSectionRegistry::routeName($section, 'edit'), [$project->id, $record->id]) }}"><i class="fa-solid fa-pencil"></i> Editar</a>
                                    <form method="POST" action="{{ route(ProjectManagerSectionRegistry::routeName($section, 'destroy'), [$project->id, $record->id]) }}" onsubmit="return confirm('Remover este registo?')">
                                        @csrf @method('DELETE')
                                        <button class="pm-btn pm-btn--compact pm-btn--danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($visibleFields) + 1 }}"><div class="pm-empty">Sem registos. Usa a ação “Novo” na barra superior para alimentar esta área.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


