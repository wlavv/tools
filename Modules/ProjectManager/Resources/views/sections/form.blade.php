@extends(config('project-manager.layout', 'layouts.app'))

@section('content')
@include('project-manager::partials.styles')
@php
    use Modules\ProjectManager\Services\ProjectManagerSectionRegistry;
    $isEdit = !empty($record);
    $action = $isEdit
        ? route(ProjectManagerSectionRegistry::routeName($section, 'update'), [$project->id, $record->id])
        : route(ProjectManagerSectionRegistry::routeName($section, 'store'), $project->id);
    $fields = $meta['fields'] ?? [];
    $textarea = $meta['textarea'] ?? [];
    $selects = $meta['selects'] ?? [];
    $booleans = $meta['booleans'] ?? [];
    $quickFields = array_slice($fields, 0, min(6, count($fields)));
    $advancedFields = array_values(array_diff($fields, $quickFields));
    $tabActive = in_array($section, ['tasks','roadmap-items'], true) ? $section : 'details';

    $renderField = function ($field) use ($record, $textarea, $selects, $booleans) {
        $value = old($field, $record->{$field} ?? null);
        $label = ucfirst(str_replace('_', ' ', $field));
        $html = '<label for="pm_'.$field.'">'.$label.'</label>';

        if (in_array($field, $booleans, true)) {
            $html .= '<select id="pm_'.$field.'" class="form-select" name="'.$field.'">';
            foreach ([0 => 'Não', 1 => 'Sim'] as $key => $option) {
                $selected = (string)$value === (string)$key ? ' selected' : '';
                $html .= '<option value="'.$key.'"'.$selected.'>'.$option.'</option>';
            }
            $html .= '</select>';
        } elseif (isset($selects[$field]) && is_array($selects[$field])) {
            $html .= '<select id="pm_'.$field.'" class="form-select" name="'.$field.'"><option value="">-</option>';
            foreach ($selects[$field] as $option) {
                $selected = (string)$value === (string)$option ? ' selected' : '';
                $html .= '<option value="'.e($option).'"'.$selected.'>'.e($option).'</option>';
            }
            $html .= '</select>';
        } elseif (in_array($field, $textarea, true) || str_contains($field, 'content') || str_contains($field, 'description') || str_contains($field, 'notes')) {
            $rows = strlen((string)$value) > 300 ? 7 : 4;
            $html .= '<textarea id="pm_'.$field.'" class="form-control" name="'.$field.'" rows="'.$rows.'">'.e($value).'</textarea>';
        } elseif (str_contains($field, '_at') || str_contains($field, 'date')) {
            $dateValue = $value ? str_replace(' ', 'T', substr((string)$value, 0, 16)) : '';
            $html .= '<input id="pm_'.$field.'" class="form-control" type="datetime-local" name="'.$field.'" value="'.e($dateValue).'">';
        } elseif (str_contains($field, 'url')) {
            $html .= '<input id="pm_'.$field.'" class="form-control" type="url" name="'.$field.'" value="'.e($value).'">';
        } elseif (str_contains($field, 'email')) {
            $html .= '<input id="pm_'.$field.'" class="form-control" type="email" name="'.$field.'" value="'.e($value).'">';
        } elseif (str_starts_with($field, 'is_') || str_ends_with($field, '_id') || in_array($field, ['priority','execution_order','expected_time','spent_time','width','height','file_size'], true)) {
            $html .= '<input id="pm_'.$field.'" class="form-control" type="number" name="'.$field.'" value="'.e($value).'">';
        } else {
            $html .= '<input id="pm_'.$field.'" class="form-control" name="'.$field.'" value="'.e($value).'">';
        }

        return $html;
    };
@endphp

<div class="lsg-content pm-wrap">
    <div class="pm-shell">
        @include('project-manager::partials.project-tabs', ['activeTab' => $tabActive])

        @if($errors->any())
            <div class="alert alert-danger mb-0">
                <strong>Verifica os dados.</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="lsg-form" class="pm-form" method="POST" action="{{ $action }}">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="pm-card">
                <div class="pm-card-title"><i class="{{ $meta['icon'] ?? 'fa-solid fa-pen-to-square' }}"></i> Dados principais</div>
                <div class="pm-card-subtitle">{{ $isEdit ? 'Editar registo' : 'Criar registo' }} em {{ $meta['label'] }}.</div>

                <div class="row g-3">
                    @foreach($quickFields as $field)
                        <div class="{{ in_array($field, $textarea, true) ? 'col-12' : 'col-md-6 col-xl-3' }}">
                            {!! $renderField($field) !!}
                        </div>
                    @endforeach
                </div>
            </div>

            @if(count($advancedFields))
                <div class="pm-card">
                    <details class="pm-details">
                        <summary><i class="fa-solid fa-sliders me-1"></i> Detalhes avançados</summary>
                        <div class="pm-details-content row g-3">
                            @foreach($advancedFields as $field)
                                <div class="{{ in_array($field, $textarea, true) ? 'col-12' : 'col-md-6 col-xl-4' }}">
                                    {!! $renderField($field) !!}
                                </div>
                            @endforeach
                        </div>
                    </details>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection
