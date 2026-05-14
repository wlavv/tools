<?php

namespace Modules\DataImportWizard\Support;

class ImportModes
{
    public const VALIDATE_ONLY = 'validate_only';
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const UPSERT = 'upsert';

    public const RESOLVE_ONLY = 'resolve_only';
    public const OPTIONAL_RESOLVE = 'optional_resolve';
    public const RESOLVE_OR_CREATE = 'resolve_or_create';
    public const RESOLVE_OR_UPDATE = 'resolve_or_update';
    public const CREATE_ONLY = 'create_only';

    public static function mainModes(): array
    {
        return [
            self::VALIDATE_ONLY,
            self::CREATE,
            self::UPDATE,
            self::UPSERT,
        ];
    }

    public static function dependencyModes(): array
    {
        return [
            self::RESOLVE_ONLY,
            self::OPTIONAL_RESOLVE,
            self::RESOLVE_OR_CREATE,
            self::RESOLVE_OR_UPDATE,
            self::CREATE_ONLY,
        ];
    }
}
