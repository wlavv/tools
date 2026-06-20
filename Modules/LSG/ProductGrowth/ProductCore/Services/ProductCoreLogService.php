<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\LSG\ProductGrowth\ProductCore\Models\CatalogLog;

class ProductCoreLogService
{
    public function log(?Model $model, string $event, string $title, ?string $message = null, array $payload = [], string $severity = 'info'): void
    {
        CatalogLog::create([
            'loggable_type' => $model ? get_class($model) : null,
            'loggable_id' => $model?->getKey(),
            'event' => $event,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'payload' => $payload,
            'user_id' => auth()->id(),
        ]);
    }
}
