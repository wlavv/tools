<?php

namespace Modules\CatalogManager\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DataImportWizard\Contracts\ImportableContract;
use Modules\DataImportWizard\Traits\HasImportContract;

class CatalogManufacturer extends Model implements ImportableContract
{
    use HasImportContract;

    protected $table = 'catalog_core_manufacturers';

    protected $guarded = [];

    public static function importKey(): string
    {
        return 'catalog_manufacturer';
    }

    public static function importLabel(): string
    {
        return 'Catalog - Fabricantes';
    }

    public static function importFields(): array
    {
        return [
            'slug' => [
                'label' => 'Slug',
                'required' => false,
                'type' => 'string',
                'example' => 'marca-xpto',
                'lookup' => true,
            ],
            'name' => [
                'label' => 'Nome',
                'required' => true,
                'type' => 'string',
                'example' => 'Marca XPTO',
            ],
            'website' => [
                'label' => 'Website',
                'required' => false,
                'type' => 'string',
                'example' => 'https://example.test',
            ],
            'active' => [
                'label' => 'Ativo',
                'required' => false,
                'type' => 'boolean',
                'example' => '1',
            ],
        ];
    }

    public static function importRules(): array
    {
        return [
            'slug' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'website' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
        ];
    }
}
