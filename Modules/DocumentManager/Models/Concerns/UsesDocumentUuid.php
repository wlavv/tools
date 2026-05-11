<?php

namespace Modules\DocumentManager\Models\Concerns;

use Illuminate\Support\Str;

trait UsesDocumentUuid
{
    protected static function bootUsesDocumentUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
