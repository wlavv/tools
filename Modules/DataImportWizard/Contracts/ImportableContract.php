<?php

namespace Modules\DataImportWizard\Contracts;

interface ImportableContract
{
    public static function importKey(): string;

    public static function importLabel(): string;

    /**
     * Field definition format:
     * [
     *   'reference' => [
     *      'label' => 'Referência',
     *      'required' => true,
     *      'type' => 'string',
     *      'example' => 'REF-001',
     *      'column' => 'reference',
     *      'lookup' => true,
     *      'fillable' => true,
     *   ],
     * ]
     */
    public static function importFields(): array;

    /**
     * Laravel validation rules indexed by import field key.
     */
    public static function importRules(): array;

    /**
     * Dependency definition format:
     * [
     *   'supplier' => [
     *      'class' => Supplier::class,
     *      'required' => true,
     *      'mode' => 'resolve_or_create',
     *      'prefix' => 'supplier',
     *      'foreign_key' => 'supplier_id',
     *   ],
     * ]
     */
    public static function importDependencies(): array;
}
