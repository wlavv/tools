<?php

namespace Modules\DataExportCenter\Examples\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Modules\DataExportCenter\Contracts\ExportableContract;
use Modules\DataExportCenter\Traits\HasExportContract;
use Modules\DataImportWizard\Contracts\ImportableContract;
use Modules\DataImportWizard\Traits\HasImportContract;

class Product extends Model implements ImportableContract, ExportableContract
{
    use HasImportContract;
    use HasExportContract;

    protected $table = 'products';

    protected $fillable = ['supplier_id', 'currency_id', 'reference', 'name', 'price', 'status'];

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

    public static function exportFields(): array
    {
        return array_merge(static::importFields(), [
            'status' => [
                'label' => 'Estado',
                'type' => 'string',
                'column' => 'status',
            ],
        ]);
    }

    public static function exportFilters(): array
    {
        return [
            'status' => [
                'label' => 'Estado',
                'type' => 'select',
                'operator' => '=',
                'column' => 'status',
            ],
            'name' => [
                'label' => 'Nome contém',
                'type' => 'string',
                'operator' => 'like',
                'column' => 'name',
            ],
        ];
    }

    public static function modifyExportQuery(Builder $query, array $context = [], array $schema = []): Builder
    {
        if (! empty($context['shop_id'])) {
            $query->where('root.shop_id', $context['shop_id']);
        }

        return $query;
    }
}
