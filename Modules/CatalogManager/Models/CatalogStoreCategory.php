<?php

namespace Modules\CatalogManager\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DataImportWizard\Contracts\ImportableContract;
use Modules\DataImportWizard\Traits\HasImportContract;

class CatalogStoreCategory extends Model implements ImportableContract
{
    use HasImportContract;

    protected $table = 'catalog_store_categories';

    protected $guarded = [];

    public static function importKey(): string
    {
        return 'catalog_store_category';
    }

    public static function importLabel(): string
    {
        return 'Catalog - Categorias de Loja';
    }

    public static function importFields(): array
    {
        return [
            'store_id' => [
                'label' => 'ID loja',
                'required' => true,
                'type' => 'integer',
                'example' => '1',
                'lookup' => true,
            ],
            'code' => [
                'label' => 'Codigo',
                'required' => true,
                'type' => 'string',
                'example' => 'cat-home',
                'lookup' => true,
            ],
            'parent_id' => [
                'label' => 'ID categoria pai',
                'required' => false,
                'type' => 'integer',
                'example' => '',
            ],
            'active' => [
                'label' => 'Ativa',
                'required' => false,
                'type' => 'boolean',
                'example' => '1',
            ],
            'position' => [
                'label' => 'Posicao',
                'required' => false,
                'type' => 'integer',
                'example' => '0',
            ],
        ];
    }

    public static function importRules(): array
    {
        return [
            'store_id' => 'required|integer',
            'code' => 'required|string|max:255',
            'parent_id' => 'nullable|integer',
            'active' => 'nullable|boolean',
            'position' => 'nullable|integer|min:0',
        ];
    }
}
