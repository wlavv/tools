<?php

namespace Modules\AuditLogCentral\Support\Facades;

use Illuminate\Support\Facades\Facade;

class AuditLog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'audit-log-central';
    }
}
