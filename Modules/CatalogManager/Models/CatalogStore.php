<?php

namespace Modules\CatalogManager\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DataImportWizard\Contracts\ImportableContract;
use Modules\DataImportWizard\Traits\HasImportContract;

class CatalogStore extends Model implements ImportableContract
{
    use HasImportContract;

    protected $table = 'catalog_stores';

    protected $guarded = [];

    protected $casts = ['settings' => 'array'];

    public static function importKey(): string
    {
        return 'catalog_store';
    }

    public static function importLabel(): string
    {
        return 'Catalog - Lojas';
    }

    public static function importFields(): array
    {
        return [
            'code' => [
                'label' => 'Codigo',
                'required' => true,
                'type' => 'string',
                'example' => 'pt-store',
                'lookup' => true,
            ],
            'name' => [
                'label' => 'Nome',
                'required' => true,
                'type' => 'string',
                'example' => 'Loja Portugal',
            ],
            'domain' => [
                'label' => 'Dominio',
                'required' => false,
                'type' => 'string',
                'example' => 'www.example.pt',
            ],
            'record_type' => [
                'label' => 'Tipo',
                'required' => false,
                'type' => 'string',
                'example' => 'store',
            ],
            'site_kind' => [
                'label' => 'Area LSG',
                'required' => false,
                'type' => 'string',
                'example' => 'service',
            ],
            'locale' => [
                'label' => 'Locale',
                'required' => false,
                'type' => 'string',
                'example' => 'pt',
            ],
            'currency' => [
                'label' => 'Moeda',
                'required' => false,
                'type' => 'string',
                'example' => 'EUR',
            ],
            'active' => [
                'label' => 'Ativa',
                'required' => false,
                'type' => 'boolean',
                'example' => '1',
            ],
        ];
    }

    public static function importRules(): array
    {
        return [
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'record_type' => 'nullable|string|in:store,domain',
            'site_kind' => 'nullable|string|in:store,service,showcase,group,labs',
            'locale' => 'nullable|string|max:8',
            'currency' => 'nullable|string|size:3',
            'active' => 'nullable|boolean',
        ];
    }
}
