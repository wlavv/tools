<?php

namespace Modules\AuditLogCentral\Support\Traits;

use Modules\AuditLogCentral\Support\Facades\AuditLog;

trait HasAuditLog
{
    public function audit(string $event, array $data = [])
    {
        return AuditLog::log(array_merge([
            'module' => $data['module'] ?? class_basename($this),
            'event' => $event,
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
        ], $data));
    }
}
