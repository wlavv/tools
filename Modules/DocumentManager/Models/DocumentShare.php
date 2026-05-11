<?php

namespace Modules\DocumentManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\DocumentManager\Models\Concerns\UsesDocumentUuid;

class DocumentShare extends Model
{
    use SoftDeletes;
    use UsesDocumentUuid;

    protected $table = 'document_core_shares';
    protected $guarded = [];

    protected $casts = [
        'permissions' => 'array',
        'can_download' => 'boolean',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];
}
