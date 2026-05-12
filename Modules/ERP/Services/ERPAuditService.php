<?php

namespace Modules\ERP\Services;

use Illuminate\Support\Facades\Auth;
use Modules\ERP\Models\ERPAuditEvent;

class ERPAuditService
{
    public function log(string $eventType, string $entityType, ?int $entityId = null, array $old = [], array $new = [], array $context = []): ERPAuditEvent
    {
        $user = Auth::user();

        return ERPAuditEvent::create([
            'event_type' => $eventType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'context' => $context ?: null,
            'ip_address' => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 500),
        ]);
    }
}
