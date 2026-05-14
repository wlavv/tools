<?php

namespace Modules\DataExportCenter\Examples\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DataImportWizard\Contracts\ImportableContract;
use Modules\DataImportWizard\Traits\HasImportContract;

class Currency extends Model implements ImportableContract
{
    use HasImportContract;

    protected $table = 'currencies';

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
            'iso' => ['label' => 'ISO', 'type' => 'string', 'column' => 'iso', 'lookup' => true],
            'name' => ['label' => 'Nome Moeda', 'type' => 'string', 'column' => 'name'],
        ];
    }

    public static function importRules(): array
    {
        return [];
    }
}
