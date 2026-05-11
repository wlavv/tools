<?php

namespace Modules\DocumentManager\Policies;

use App\Models\User;
use Modules\DocumentManager\Models\Document;

class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        return $document->visibility !== 'private' || (int) $document->created_by === (int) $user->id;
    }

    public function update(User $user, Document $document): bool
    {
        return !$document->is_locked && !$document->is_immutable && (int) $document->created_by === (int) $user->id;
    }
}
