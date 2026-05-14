<?php

namespace Modules\DataExportCenter\Contracts;

interface ExportableContract
{
    public static function exportKey(): string;

    public static function exportLabel(): string;

    /**
     * Field definition format:
     * [
     *   'reference' => [
     *      'label' => 'Referência',
     *      'type' => 'string',
     *      'column' => 'reference',
     *      'exportable' => true,
     *      'select' => null, // optional trusted SQL expression
     *   ],
     * ]
     */
    public static function exportFields(): array;
}
