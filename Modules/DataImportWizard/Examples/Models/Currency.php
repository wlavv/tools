<?php

namespace Modules\DataImportWizard\Examples\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DataImportWizard\Contracts\ImportableContract;
use Modules\DataImportWizard\Traits\HasImportContract;

class Currency extends Model implements ImportableContract
{
    use HasImportContract;

    protected $table = 'currencies';

    protected $fillable = ['iso', 'name'];

    public static function importKey(): string
    {
        return 'currency';
    }

    public static function importLabel(): string
    {
        return 'Moeda';
    }

    public static function importFields(): array
    {
        return [
            'iso' => [
                'label' => 'ISO Moeda',
                'required' => true,
                'type' => 'string',
                'example' => 'EUR',
                'column' => 'iso',
                'lookup' => true,
            ],
        ];
    }

    public static function importRules(): array
    {
        return [
            'iso' => 'required|string|size:3',
        ];
    }
}
