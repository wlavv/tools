<?php

namespace Modules\AuditLogCentral\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Modules\AuditLogCentral\Models\AuditLog;

class AuditLogService
{
    public function log(array $data): AuditLog
    {
        $user = Auth::user();
        $changes = $data['changes'] ?? [];
        $tags = $data['tags'] ?? [];
        $relations = $data['relations'] ?? [];

        $log = AuditLog::create([
            'uuid' => $data['uuid'] ?? (string) Str::uuid(),
            'module' => $data['module'] ?? 'system',
            'event' => $data['event'] ?? 'event',
            'action' => $data['action'] ?? null,
            'severity' => $data['severity'] ?? config('audit-log-central.default_severity', 'info'),
            'status' => $data['status'] ?? 'success',
            'auditable_type' => $data['auditable_type'] ?? null,
            'auditable_id' => $data['auditable_id'] ?? null,
            'user_id' => $data['user_id'] ?? optional($user)->id,
            'user_name' => $data['user_name'] ?? optional($user)->name,
            'user_email' => $data['user_email'] ?? optional($user)->email,
            'ip_address' => $data['ip_address'] ?? Request::ip(),
            'user_agent' => $data['user_agent'] ?? substr((string) Request::userAgent(), 0, 512),
            'request_method' => $data['request_method'] ?? Request::method(),
            'request_url' => $data['request_url'] ?? substr((string) Request::fullUrl(), 0, 1024),
            'source' => $data['source'] ?? 'web',
            'correlation_id' => $data['correlation_id'] ?? Request::header('X-Correlation-ID'),
            'metadata' => $this->sanitize($data['metadata'] ?? []),
            'payload' => $this->sanitize($data['payload'] ?? Arr::except($data, ['changes', 'tags', 'relations'])),
            'occurred_at' => $data['occurred_at'] ?? now(),
        ]);

        foreach ($changes as $field => $change) {
            if (is_array($change) && array_key_exists('old', $change) && array_key_exists('new', $change)) {
                $log->changes()->create([
                    'field' => $field,
                    'old_value' => $this->wrapValue($this->sanitize($change['old'])),
                    'new_value' => $this->wrapValue($this->sanitize($change['new'])),
                    'change_type' => $change['type'] ?? 'updated',
                ]);
            }
        }

        foreach (array_unique($tags) as $tag) {
            $log->tags()->firstOrCreate(['tag' => strtoupper((string) $tag)]);
        }

        foreach ($relations as $relation) {
            if (!empty($relation['type']) && isset($relation['id'])) {
                $log->relations()->create([
                    'related_type' => $relation['type'],
                    'related_id' => (string) $relation['id'],
                    'label' => $relation['label'] ?? null,
                ]);
            }
        }

        return $log->load(['changes', 'tags', 'relations']);
    }

    public function modelUpdated(string $module, object $model, array $old, array $new, array $context = []): AuditLog
    {
        $changes = [];
        foreach ($new as $field => $value) {
            $previous = $old[$field] ?? null;
            if ($previous !== $value) {
                $changes[$field] = ['old' => $previous, 'new' => $value, 'type' => 'updated'];
            }
        }

        return $this->log(array_merge($context, [
            'module' => $module,
            'event' => 'model.updated',
            'action' => 'update',
            'auditable_type' => get_class($model),
            'auditable_id' => method_exists($model, 'getKey') ? $model->getKey() : null,
            'changes' => $changes,
        ]));
    }

    protected function sanitize($value)
    {
        $sensitiveKeys = array_map('strtolower', config('audit-log-central.sensitive_keys', []));

        if (is_array($value)) {
            $clean = [];
            foreach ($value as $key => $item) {
                $keyString = strtolower((string) $key);
                $clean[$key] = in_array($keyString, $sensitiveKeys, true) ? '[REDACTED]' : $this->sanitize($item);
            }
            return $clean;
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return $this->sanitize($value->toArray());
        }

        return $value;
    }

    protected function wrapValue($value): array
    {
        return ['value' => $value];
    }
}
