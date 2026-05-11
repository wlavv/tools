<?php

namespace Modules\DocumentManager\Services;

use Modules\DocumentManager\Models\Document;

class PermissionService
{
    public function can(?int $userId, string $permission, Document $document): bool
    {
        if (!$userId) {
            return false;
        }

        if ((int) $document->created_by === (int) $userId) {
            return true;
        }

        return $document->visibility !== 'private' && in_array($permission, ['view', 'download'], true);
    }
}
