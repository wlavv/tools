<?php

namespace Modules\DataImportWizard\Examples\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\DataImportWizard\Contracts\ImportableContract;
use Modules\DataImportWizard\Traits\HasImportContract;

class Supplier extends Model implements ImportableContract
{
    use HasImportContract;

    protected $table = 'suppliers';

    protected $fillable = ['reference', 'name', 'email'];

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
            'reference' => [
                'label' => 'Referência Fornecedor',
                'required' => true,
                'type' => 'string',
                'example' => 'SUP-001',
                'column' => 'reference',
                'lookup' => true,
            ],
            'name' => [
                'label' => 'Nome Fornecedor',
                'required' => true,
                'type' => 'string',
                'example' => 'Fornecedor XPTO',
                'column' => 'name',
            ],
            'email' => [
                'label' => 'Email Fornecedor',
                'required' => false,
                'type' => 'email',
                'example' => 'supplier@example.test',
                'column' => 'email',
            ],
        ];
    }

    public static function importRules(): array
    {
        return [
            'reference' => 'required|string|max:64',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ];
    }
}
