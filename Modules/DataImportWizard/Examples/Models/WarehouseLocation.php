<?php

namespace Modules\DataImportWizard\Examples\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DataImportWizard\Contracts\ImportableContract;
use Modules\DataImportWizard\Traits\HasImportContract;

class WarehouseLocation extends Model implements ImportableContract
{
    use HasImportContract;

    protected $table = 'warehouse_locations';

    protected $fillable = ['supplier_id', 'code', 'name'];

    public static function importKey(): string
    {
        return 'warehouse_location';
    }

    public static function importLabel(): string
    {
        return 'Localização de Armazém';
    }

    public static function importFields(): array
    {
        return [
            'code' => [
                'label' => 'Código Localização',
                'required' => true,
                'type' => 'string',
                'example' => 'A1-B2',
                'column' => 'code',
                'lookup' => true,
            ],
            'name' => [
                'label' => 'Nome Localização',
                'required' => true,
                'type' => 'string',
                'example' => 'Corredor A1, Prateleira B2',
                'column' => 'name',
            ],
        ];
    }

    public static function importRules(): array
    {
        return [
            'code' => 'required|string|max:64',
            'name' => 'required|string|max:255',
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
        ];
    }
}
