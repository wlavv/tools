<?php

namespace Modules\DataImportWizard\Examples\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DataImportWizard\Contracts\ImportableContract;
use Modules\DataImportWizard\Traits\HasImportContract;

class Product extends Model implements ImportableContract
{
    use HasImportContract;

    protected $table = 'products';

    protected $fillable = ['supplier_id', 'currency_id', 'reference', 'name', 'price'];

    public static function importKey(): string
    {
        return 'product';
    }

    public static function importLabel(): string
    {
        return 'Produto';
    }

    public static function importFields(): array
    {
        return [
            'reference' => [
                'label' => 'Referência Produto',
                'required' => true,
                'type' => 'string',
                'example' => 'PRD-001',
                'column' => 'reference',
                'lookup' => true,
            ],
            'name' => [
                'label' => 'Nome Produto',
                'required' => true,
                'type' => 'string',
                'example' => 'Produto XPTO',
                'column' => 'name',
            ],
            'price' => [
                'label' => 'Preço',
                'required' => true,
                'type' => 'decimal',
                'example' => '12.95',
                'column' => 'price',
            ],
        ];
    }

    public static function importRules(): array
    {
        return [
            'reference' => 'required|string|max:64',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ];
    }

    public static function importDependencies(): array
    {
        return [
            'supplier' => [
                'class' => Supplier::class,
                'required' => true,
                'mode' => 'resolve_or_create',
                'prefix' => 'supplier',
                'foreign_key' => 'supplier_id',
            ],
            'currency' => [
                'class' => Currency::class,
                'required' => true,
                'mode' => 'resolve_only',
                'prefix' => 'currency',
                'foreign_key' => 'currency_id',
            ],
        ];
    }
}
