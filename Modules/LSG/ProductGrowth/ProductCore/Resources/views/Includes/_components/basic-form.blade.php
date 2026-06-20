@php($routeBase = 'product_growth.product_core.' . $type)
<div class="product-core-card p-3"><form method="POST" action="{{ $item->exists ? route($routeBase . '.update', $item) : route($routeBase . '.store') }}" class="pc-form">@csrf @if($item->exists)@method('PUT')@endif
<div class="pc-form-grid">
@foreach($fields as $field=>$label)
<div class="{{ in_array($field,['description','notes']) ? 'pc-form-grid-1' : '' }}"><label class="pc-label">{{ $label }}</label>@if(in_array($field,['description','notes']))<textarea class="pc-textarea" name="{{ $field }}" rows="4">{{ old($field, $item->$field) }}</textarea>@else<input class="pc-input" name="{{ $field }}" value="{{ old($field, $item->$field) }}">@endif</div>
@endforeach
<div class="pc-form-grid-1"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))> Ativo</label></div>
</div>
<div><button class="lsg-action-btn lsg-action-btn--primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button></div></form></div>
