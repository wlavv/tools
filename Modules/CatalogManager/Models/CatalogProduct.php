<?php

namespace Modules\CatalogManager\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DataImportWizard\Contracts\ImportableContract;
use Modules\DataImportWizard\Traits\HasImportContract;

class CatalogProduct extends Model implements ImportableContract
{
    use HasImportContract;

    protected $table = 'catalog_core_products';

    protected $guarded = [];

    public static function importKey(): string
    {
        return 'catalog_product';
    }

    public static function importLabel(): string
    {
        return 'Catalog - Produtos Base';
    }

    public static function importFields(): array
    {
        return [
            'internal_sku' => [
                'label' => 'SKU interno',
                'required' => false,
                'type' => 'string',
                'example' => 'SKU-001',
                'lookup' => true,
            ],
            'reference' => [
                'label' => 'Referencia',
                'required' => false,
                'type' => 'string',
                'example' => 'REF-001',
            ],
            'ean13' => [
                'label' => 'EAN13',
                'required' => false,
                'type' => 'string',
                'example' => '5600000000000',
            ],
            'name' => [
                'label' => 'Nome',
                'required' => true,
                'type' => 'string',
                'example' => 'Produto XPTO',
            ],
            'manufacturer_id' => [
                'label' => 'ID fabricante',
                'required' => false,
                'type' => 'integer',
                'example' => '1',
            ],
            'type' => [
                'label' => 'Tipo',
                'required' => false,
                'type' => 'string',
                'example' => 'simple',
            ],
            'status' => [
                'label' => 'Estado',
                'required' => false,
                'type' => 'string',
                'example' => 'draft',
            ],
            'weight' => [
                'label' => 'Peso',
                'required' => false,
                'type' => 'decimal',
                'example' => '1.250',
            ],
            'width' => [
                'label' => 'Largura',
                'required' => false,
                'type' => 'decimal',
                'example' => '10.000',
            ],
            'height' => [
                'label' => 'Altura',
                'required' => false,
                'type' => 'decimal',
                'example' => '20.000',
            ],
            'depth' => [
                'label' => 'Profundidade',
                'required' => false,
                'type' => 'decimal',
                'example' => '5.000',
            ],
            'housing' => [
                'label' => 'Housing',
                'required' => false,
                'type' => 'string',
                'example' => 'BOX',
            ],
            'internal_notes' => [
                'label' => 'Notas internas',
                'required' => false,
                'type' => 'string',
                'example' => 'Notas de importacao',
            ],
        ];
    }

    public static function importRules(): array
    {
        return [
            'internal_sku' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'ean13' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'manufacturer_id' => 'nullable|integer',
            'type' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'depth' => 'nullable|numeric',
            'housing' => 'nullable|string|max:255',
            'internal_notes' => 'nullable|string',
        ];
    }
}
