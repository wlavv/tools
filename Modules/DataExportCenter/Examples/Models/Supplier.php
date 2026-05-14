<?php

namespace Modules\DataExportCenter\Examples\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DataImportWizard\Contracts\ImportableContract;
use Modules\DataImportWizard\Traits\HasImportContract;

class Supplier extends Model implements ImportableContract
{
    use HasImportContract;

    protected $table = 'suppliers';

    public static function importKey(): string
    {
        return 'supplier';
    }

    public static function importLabel(): string
    {
        return 'Fornecedor';
    }

    public static function importFields(): array
    {
        return [
            'reference' => ['label' => 'Referência Fornecedor', 'type' => 'string', 'column' => 'reference', 'lookup' => true],
            'name' => ['label' => 'Nome Fornecedor', 'type' => 'string', 'column' => 'name'],
            'email' => ['label' => 'Email Fornecedor', 'type' => 'email', 'column' => 'email'],
        ];
    }

    public static function importRules(): array
    {
        return [];
    }
}
