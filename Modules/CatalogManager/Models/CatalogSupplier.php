<?php

namespace Modules\CatalogManager\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DataImportWizard\Contracts\ImportableContract;
use Modules\DataImportWizard\Traits\HasImportContract;

class CatalogSupplier extends Model implements ImportableContract
{
    use HasImportContract;

    protected $table = 'catalog_core_suppliers';

    protected $guarded = [];

    protected $fillable = [];

    public static function importKey(): string
    {
        return 'catalog_supplier';
    }

    public static function importLabel(): string
    {
        return 'Catalog - Fornecedores';
    }

    public static function importFields(): array
    {
        return [
            'code' => [
                'label' => 'Codigo',
                'required' => true,
                'type' => 'string',
                'example' => 'SUP-001',
                'lookup' => true,
            ],
            'name' => [
                'label' => 'Nome',
                'required' => true,
                'type' => 'string',
                'example' => 'Fornecedor XPTO',
            ],
            'email' => [
                'label' => 'Email',
                'required' => false,
                'type' => 'email',
                'example' => 'supplier@example.test',
            ],
            'phone' => [
                'label' => 'Telefone',
                'required' => false,
                'type' => 'string',
                'example' => '+351 210 000 000',
            ],
            'currency' => [
                'label' => 'Moeda',
                'required' => false,
                'type' => 'string',
                'example' => 'EUR',
            ],
            'lead_time_days' => [
                'label' => 'Lead time dias',
                'required' => false,
                'type' => 'integer',
                'example' => '7',
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
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'currency' => 'nullable|string|size:3',
            'lead_time_days' => 'nullable|integer|min:0',
            'active' => 'nullable|boolean',
        ];
    }
}
