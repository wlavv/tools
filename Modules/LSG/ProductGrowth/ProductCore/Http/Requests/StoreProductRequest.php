<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'internal_sku' => ['nullable','string','max:120'],
            'reference' => ['nullable','string','max:120'],
            'ean' => ['nullable','string','max:80'],
            'mpn' => ['nullable','string','max:120'],
            'name' => ['required','string','max:220'],
            'description' => ['nullable','string'],
            'product_type' => ['nullable','string','max:80'],
            'assigned_workflow' => ['nullable','string','max:120'],
            'base_cost' => ['nullable','numeric','min:0'],
            'base_price' => ['nullable','numeric','min:0'],
            'weight' => ['nullable','numeric','min:0'],
            'width' => ['nullable','numeric','min:0'],
            'height' => ['nullable','numeric','min:0'],
            'depth' => ['nullable','numeric','min:0'],
            'status' => ['nullable','in:draft,in_review,approved,ready_to_sync,synced,needs_resync,archived,blocked'],
            'is_active' => ['nullable','boolean'],
            'store_ids' => ['nullable','array'],
            'store_ids.*' => ['exists:lsg_sites,id'],
            'category_ids' => ['nullable','array'],
            'category_ids.*' => ['nullable','integer','exists:catalog_store_categories,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->has('is_active') ? $this->boolean('is_active') : false]);
    }
}
